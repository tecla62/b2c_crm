<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssetItem;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private array $reportTypes = [
        'sales' => 'Invoice Sales',
        'quotations' => 'Quotation Pipeline',
        'payments' => 'Payment Collection',
        'expenses' => 'Expense Register',
        'customers' => 'Customer Ledger',
        'assets' => 'Asset Stock',
        'sales-analysis' => 'Sales Analysis',
        'revenue-analysis' => 'Revenue Comparison',
        'receivables' => 'Outstanding Receivables',
        'payment-trends' => 'Payment Trends',
        'expense-analysis' => 'Expense Breakdown',
        'customer-performance' => 'Customer Performance',
    ];

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $report = $this->buildReport($filters);
        $customers = Customer::orderBy('name')->get(['id', 'name', 'company_name']);

        return view('admin.reports.index', [
            'reportTypes' => $this->reportTypes,
            'filters' => $filters,
            'report' => $report,
            'customers' => $customers,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $report = $this->buildReport($filters, false);
        $filename = $this->filename($filters['type'], 'csv');

        return response()->streamDownload(function () use ($report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $report['exportHeaders'] ?? $report['headers']);

            foreach ($report['rows'] as $row) {
                fputcsv($handle, array_map(fn($value) => is_scalar($value) ? $value : '', $row['export']));
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function exportPdf(Request $request)
    {
        $filters = $this->filters($request);
        $report = $this->buildReport($filters, false);
        $filename = $this->filename($filters['type'], 'pdf');
        $columnCount = count($report['exportHeaders'] ?? $report['headers'] ?? []);
        $orientation = $columnCount > 7 ? 'landscape' : 'portrait';

        $pdf = Pdf::loadView('admin.reports.pdf', [
            'reportTypes' => $this->reportTypes,
            'filters' => $filters,
            'report' => $report,
            'orientation' => $orientation,
        ])->setPaper('a4', $orientation);

        return $pdf->download($filename);
    }

    private function filters(Request $request): array
    {
        $type = $request->get('type', 'sales');
        if (!array_key_exists($type, $this->reportTypes)) {
            $type = 'sales';
        }

        return [
            'type' => $type,
            'from' => $request->get('from', now()->startOfMonth()->toDateString()),
            'to' => $request->get('to', now()->endOfMonth()->toDateString()),
            'customer_id' => $request->get('customer_id'),
            'status' => $request->get('status'),
            'category' => $request->get('category'),
            'stock' => $request->get('stock'),
            'sort' => $request->get('sort', 'date_desc'),
            'search' => $request->get('search'),
            'payment_method' => $request->get('payment_method'),
            'condition' => $request->get('condition'),
            'pricing_mode' => $request->get('pricing_mode'),
        ];
    }

    private function buildReport(array $filters, bool $paginate = true): array
    {
        return match ($filters['type']) {
            'quotations' => $this->quotationReport($filters, $paginate),
            'payments' => $this->paymentReport($filters, $paginate),
            'expenses' => $this->expenseReport($filters, $paginate),
            'customers' => $this->customerReport($filters, $paginate),
            'assets' => $this->assetReport($filters, $paginate),
            'sales-analysis' => $this->salesAnalysisReport($filters, $paginate),
            'revenue-analysis' => $this->revenueAnalysisReport($filters, $paginate),
            'receivables' => $this->receivablesReport($filters, $paginate),
            'payment-trends' => $this->paymentTrendsReport($filters, $paginate),
            'expense-analysis' => $this->expenseAnalysisReport($filters, $paginate),
            'customer-performance' => $this->customerPerformanceReport($filters, $paginate),
            default => $this->salesReport($filters, $paginate),
        };
    }

    private function salesReport(array $filters, bool $paginate): array
    {
        $query = Invoice::with('customer', 'quotation')->withSum([
            'transactions as paid_total' => fn($q) => $q->where('status', 'cleared')->where('type', 'payment')->where('direction', 'income')
        ], 'amount');

        $this->dateFilter($query, 'date', $filters);
        $this->customerFilter($query, $filters);
        $this->statusFilter($query, $filters);
        $this->searchInvoices($query, $filters);
        if (!empty($filters['pricing_mode']) && \Illuminate\Support\Facades\Schema::hasColumn('invoices', 'pricing_mode')) {
            $query->where('pricing_mode', $filters['pricing_mode']);
        }
        $this->sort($query, $filters, 'date', 'total');

        $records = $paginate ? $query->paginate(20)->withQueryString() : $query->get();
        $collection = $paginate ? $records->getCollection() : $records;

        $rows = $collection->map(function ($invoice) {
            $paid = (float) ($invoice->paid_total ?? 0);
            $outstanding = max(0, (float) $invoice->total - $paid);

            return [
                'view' => [
                    $invoice->date?->format(setting('date_format', 'd/m/Y')),
                    $invoice->invoice_number,
                    $invoice->customer?->name ?? 'N/A',
                    $invoice->due_date?->format(setting('date_format', 'd/m/Y')) ?? 'N/A',
                    ucfirst($invoice->status),
                    format_currency((float) $invoice->total),
                    format_currency($paid),
                    format_currency($outstanding),
                ],
                'export' => [
                    $invoice->date?->toDateString(),
                    $invoice->invoice_number,
                    $invoice->quotation?->quotation_number,
                    $invoice->customer?->name ?? '',
                    $invoice->customer?->company_name ?? '',
                    $invoice->customer?->email ?? '',
                    $invoice->customer?->mobile ?? '',
                    $invoice->subject,
                    $invoice->pricing_mode,
                    $invoice->due_date?->toDateString(),
                    $invoice->status,
                    (float) $invoice->subtotal,
                    (float) $invoice->discount,
                    (float) $invoice->tax_amount,
                    (float) $invoice->total,
                    $paid,
                    $outstanding,
                    $invoice->created_at?->toDateTimeString(),
                ],
            ];
        });

        return $this->pack('Invoice Sales', ['Date', 'Invoice', 'Customer', 'Due Date', 'Status', 'Total', 'Paid', 'Outstanding'], $rows, $records, [
            'Total Invoices' => $collection->count(),
            'Invoice Value' => format_currency((float) $collection->sum('total')),
            'Paid' => format_currency((float) $collection->sum(fn($invoice) => $invoice->paid_total ?? 0)),
            'Outstanding' => format_currency((float) $rows->sum(fn($row) => (float) $row['export'][16])),
        ], ['Date', 'Invoice', 'Quotation', 'Customer', 'Company', 'Email', 'Mobile', 'Subject', 'Pricing Mode', 'Due Date', 'Status', 'Subtotal', 'Discount', 'Tax', 'Total', 'Paid', 'Outstanding', 'Created At']);
    }

    private function quotationReport(array $filters, bool $paginate): array
    {
        $query = Quotation::with('customer');
        $this->dateFilter($query, 'date', $filters);
        $this->customerFilter($query, $filters);
        $this->statusFilter($query, $filters);
        $this->searchQuotations($query, $filters);
        if (!empty($filters['pricing_mode']) && \Illuminate\Support\Facades\Schema::hasColumn('invoices', 'pricing_mode')) {
            $query->where('pricing_mode', $filters['pricing_mode']);
        }
        $this->sort($query, $filters, 'date', 'total');

        $records = $paginate ? $query->paginate(20)->withQueryString() : $query->get();
        $collection = $paginate ? $records->getCollection() : $records;

        $rows = $collection->map(fn($quotation) => [
            'view' => [
                $quotation->date?->format(setting('date_format', 'd/m/Y')),
                $quotation->quotation_number,
                $quotation->customer?->name ?? 'N/A',
                ucfirst($quotation->status),
                $quotation->valid_until?->format(setting('date_format', 'd/m/Y')) ?? 'N/A',
                format_currency((float) $quotation->total),
            ],
            'export' => [
                $quotation->date?->toDateString(),
                $quotation->quotation_number,
                $quotation->customer?->name ?? '',
                $quotation->customer?->company_name ?? '',
                $quotation->customer?->email ?? '',
                $quotation->customer?->mobile ?? '',
                $quotation->subject,
                $quotation->pricing_mode,
                $quotation->status,
                $quotation->valid_until?->toDateString(),
                (float) $quotation->subtotal,
                (float) $quotation->discount,
                (float) $quotation->tax_amount,
                (float) $quotation->total,
                $quotation->created_at?->toDateTimeString(),
            ],
        ]);

        return $this->pack('Quotation Pipeline', ['Date', 'Quotation', 'Customer', 'Status', 'Valid Until', 'Total'], $rows, $records, [
            'Records' => $collection->count(),
            'Pipeline Value' => format_currency((float) $collection->sum('total')),
            'Approved' => $collection->whereIn('status', ['accepted', 'approved'])->count(),
            'Open' => $collection->whereIn('status', ['draft', 'sent'])->count(),
        ], ['Date', 'Quotation', 'Customer', 'Company', 'Email', 'Mobile', 'Subject', 'Pricing Mode', 'Status', 'Valid Until', 'Subtotal', 'Discount', 'Tax', 'Total', 'Created At']);
    }

    private function paymentReport(array $filters, bool $paginate): array
    {
        $query = Transaction::with('customer', 'invoice')->where('type', 'payment')->where('direction', 'income');
        $this->dateFilter($query, 'transaction_date', $filters);
        $this->customerFilter($query, $filters);
        $this->statusFilter($query, $filters);
        $this->searchPayments($query, $filters);
        if ($filters['payment_method']) {
            $query->where('payment_method', $filters['payment_method']);
        }
        $this->sort($query, $filters, 'transaction_date', 'amount');

        $records = $paginate ? $query->paginate(20)->withQueryString() : $query->get();
        $collection = $paginate ? $records->getCollection() : $records;

        $rows = $collection->map(fn($payment) => [
            'view' => [
                $payment->transaction_date?->format(setting('date_format', 'd/m/Y')),
                $payment->transaction_number,
                $payment->invoice?->invoice_number ?? 'N/A',
                $payment->customer?->name ?? 'N/A',
                ucfirst(str_replace('_', ' ', $payment->payment_method)),
                ucfirst($payment->status),
                format_currency((float) $payment->amount),
            ],
            'export' => [
                $payment->transaction_date?->toDateString(),
                $payment->transaction_number,
                $payment->invoice?->invoice_number ?? '',
                $payment->customer?->name ?? '',
                $payment->customer?->company_name ?? '',
                $payment->customer?->email ?? '',
                $payment->customer?->mobile ?? '',
                $payment->payment_method,
                $payment->reference_no,
                $payment->status,
                (float) $payment->amount,
                $payment->notes,
                $payment->receipt_sent_at?->toDateTimeString(),
                $payment->created_at?->toDateTimeString(),
            ],
        ]);

        return $this->pack('Payment Collection', ['Date', 'Payment', 'Invoice', 'Customer', 'Method', 'Status', 'Amount'], $rows, $records, [
            'Records' => $collection->count(),
            'Collected' => format_currency((float) $collection->where('status', 'cleared')->sum('amount')),
            'Pending' => format_currency((float) $collection->where('status', 'pending')->sum('amount')),
            'Failed/Cancelled' => $collection->whereIn('status', ['failed', 'cancelled'])->count(),
        ], ['Date', 'Payment', 'Invoice', 'Customer', 'Company', 'Email', 'Mobile', 'Method', 'Reference', 'Status', 'Amount', 'Notes', 'Receipt Sent At', 'Created At']);
    }

    private function expenseReport(array $filters, bool $paginate): array
    {
        $query = Expense::with('customer', 'invoice');
        $this->dateFilter($query, 'expense_date', $filters);
        $this->customerFilter($query, $filters);
        $this->statusFilter($query, $filters);
        $this->searchExpenses($query, $filters);
        if ($filters['category']) {
            $query->where('category', $filters['category']);
        }
        if ($filters['payment_method']) {
            $query->where('payment_method', $filters['payment_method']);
        }
        $this->sort($query, $filters, 'expense_date', 'amount');

        $records = $paginate ? $query->paginate(20)->withQueryString() : $query->get();
        $collection = $paginate ? $records->getCollection() : $records;

        $rows = $collection->map(fn($expense) => [
            'view' => [
                $expense->expense_date?->format(setting('date_format', 'd/m/Y')),
                $expense->expense_number,
                $expense->category,
                $expense->vendor_name ?: 'N/A',
                $expense->customer?->name ?? 'N/A',
                ucfirst($expense->status),
                format_currency((float) $expense->amount),
            ],
            'export' => [
                $expense->expense_date?->toDateString(),
                $expense->expense_number,
                $expense->invoice?->invoice_number ?? '',
                $expense->category,
                $expense->vendor_name,
                $expense->customer?->name ?? '',
                $expense->customer?->company_name ?? '',
                $expense->customer?->email ?? '',
                $expense->customer?->mobile ?? '',
                $expense->payment_method,
                $expense->reference_no,
                $expense->status,
                (float) $expense->amount,
                $expense->notes,
                $expense->created_at?->toDateTimeString(),
            ],
        ]);

        return $this->pack('Expense Register', ['Date', 'Expense', 'Category', 'Vendor', 'Customer', 'Status', 'Amount'], $rows, $records, [
            'Records' => $collection->count(),
            'Approved/Paid' => format_currency((float) $collection->whereIn('status', ['approved', 'paid'])->sum('amount')),
            'Pending' => format_currency((float) $collection->where('status', 'pending')->sum('amount')),
            'Categories' => $collection->pluck('category')->filter()->unique()->count(),
        ], ['Date', 'Expense', 'Invoice', 'Category', 'Vendor', 'Customer', 'Company', 'Email', 'Mobile', 'Method', 'Reference', 'Status', 'Amount', 'Notes', 'Created At']);
    }

    private function customerReport(array $filters, bool $paginate): array
    {
        $paymentScope = function ($q) use ($filters) {
            $q->where('status', 'cleared')->where('type', 'payment')->where('direction', 'income');
            $this->dateFilter($q, 'transaction_date', $filters);
        };
        $expenseScope = function ($q) use ($filters) {
            $q->whereIn('status', ['approved', 'paid']);
            $this->dateFilter($q, 'expense_date', $filters);
        };

        $query = Customer::query()
            ->withSum(['transactions as payment_total' => $paymentScope], 'amount')
            ->withCount(['transactions as payment_count' => $paymentScope])
            ->withSum(['expenses as expense_total' => $expenseScope], 'amount')
            ->withCount(['expenses as expense_count' => $expenseScope]);

        if ($filters['customer_id']) {
            $query->where('id', $filters['customer_id']);
        }
        $this->statusFilter($query, $filters);
        if ($filters['search']) {
            $s = $filters['search'];
            $query->where(fn($q) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('company_name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('mobile', 'like', "%{$s}%")
                ->orWhere('gst_number', 'like', "%{$s}%"));
        }
        $query->orderByRaw('(COALESCE(payment_total, 0) - COALESCE(expense_total, 0)) DESC')->orderBy('name');

        $records = $paginate ? $query->paginate(20)->withQueryString() : $query->get();
        $collection = $paginate ? $records->getCollection() : $records;

        $rows = $collection->map(function ($customer) {
            $payments = (float) ($customer->payment_total ?? 0);
            $expenses = (float) ($customer->expense_total ?? 0);

            return [
                'view' => [
                    $customer->name,
                    $customer->company_name ?: 'N/A',
                    $customer->mobile ?: 'N/A',
                    ucfirst($customer->status),
                    $customer->payment_count ?? 0,
                    format_currency($payments),
                    format_currency($expenses),
                    format_currency($payments - $expenses),
                ],
                'export' => [
                    $customer->name,
                    $customer->company_name,
                    $customer->email,
                    $customer->mobile,
                    $customer->gst_number,
                    $customer->city,
                    $customer->state,
                    $customer->source,
                    $customer->type,
                    $customer->status,
                    $customer->payment_count ?? 0,
                    $customer->expense_count ?? 0,
                    $payments,
                    $expenses,
                    $payments - $expenses,
                    $customer->created_at?->toDateTimeString(),
                ],
            ];
        });

        return $this->pack('Customer Ledger', ['Customer', 'Company', 'Mobile', 'Status', 'Payments', 'Paid', 'Expenses', 'Net'], $rows, $records, [
            'Customers' => $collection->count(),
            'Paid' => format_currency((float) $collection->sum(fn($customer) => $customer->payment_total ?? 0)),
            'Expenses' => format_currency((float) $collection->sum(fn($customer) => $customer->expense_total ?? 0)),
            'Net' => format_currency((float) $rows->sum(fn($row) => $row['export'][14])),
        ], ['Customer', 'Company', 'Email', 'Mobile', 'GST', 'City', 'State', 'Source', 'Type', 'Status', 'Payment Count', 'Expense Count', 'Paid', 'Expenses', 'Net', 'Created At']);
    }

    private function assetReport(array $filters, bool $paginate): array
    {
        $query = AssetItem::query();
        $this->statusFilter($query, $filters);
        if ($filters['category']) {
            $query->where('category', $filters['category']);
        }
        if ($filters['condition']) {
            $query->where('condition', $filters['condition']);
        }
        if ($filters['search']) {
            $s = $filters['search'];
            $query->where(fn($q) => $q->where('asset_code', 'like', "%{$s}%")
                ->orWhere('name', 'like', "%{$s}%")
                ->orWhere('category', 'like', "%{$s}%")
                ->orWhere('location', 'like', "%{$s}%"));
        }
        if ($filters['stock'] === 'low') {
            $query->whereRaw('(total_quantity - issued_quantity - damaged_quantity - maintenance_quantity - lost_quantity) <= minimum_quantity');
        } elseif ($filters['stock'] === 'out') {
            $query->whereRaw('(total_quantity - issued_quantity - damaged_quantity - maintenance_quantity - lost_quantity) <= 0');
        } elseif ($filters['stock'] === 'available') {
            $query->whereRaw('(total_quantity - issued_quantity - damaged_quantity - maintenance_quantity - lost_quantity) > 0');
        }
        $query->orderBy('name');

        $records = $paginate ? $query->paginate(20)->withQueryString() : $query->get();
        $collection = $paginate ? $records->getCollection() : $records;

        $rows = $collection->map(fn($asset) => [
            'view' => [
                $asset->asset_code,
                $asset->name,
                $asset->category ?: 'N/A',
                ucfirst($asset->status),
                $asset->total_quantity,
                $asset->issued_quantity,
                $asset->available_quantity,
                $asset->damaged_quantity + $asset->maintenance_quantity + $asset->lost_quantity,
            ],
            'export' => [
                $asset->asset_code,
                $asset->name,
                $asset->category,
                $asset->unit,
                $asset->location,
                $asset->condition,
                $asset->status,
                $asset->total_quantity,
                $asset->issued_quantity,
                $asset->available_quantity,
                $asset->damaged_quantity,
                $asset->maintenance_quantity,
                $asset->lost_quantity,
                $asset->minimum_quantity,
                $asset->notes,
                $asset->created_at?->toDateTimeString(),
            ],
        ]);

        return $this->pack('Asset Stock', ['Code', 'Asset', 'Category', 'Status', 'Total', 'Issued', 'Available', 'Problem'], $rows, $records, [
            'Assets' => $collection->count(),
            'Total Qty' => $collection->sum('total_quantity'),
            'Issued' => $collection->sum('issued_quantity'),
            'Available' => $collection->sum(fn($asset) => $asset->available_quantity),
        ], ['Code', 'Asset', 'Category', 'Unit', 'Location', 'Condition', 'Status', 'Total', 'Issued', 'Available', 'Damaged', 'Maintenance', 'Lost', 'Minimum Alert', 'Notes', 'Created At']);
    }

    private function salesAnalysisReport(array $filters, bool $paginate): array
    {
        $query = Invoice::selectRaw(
                'DATE(date) as date, COUNT(*) as count, SUM(total) as amount, SUM(COALESCE((SELECT SUM(amount) FROM transactions WHERE transactions.invoice_id = invoices.id AND status = ? AND type = ? AND direction = ?), 0)) as paid',
                ['cleared', 'payment', 'income']
            )
            ->whereDate('date', '>=', $filters['from'])
            ->whereDate('date', '<=', $filters['to'])
            ->groupBy('date')
            ->orderByDesc('date');

        $records = $paginate ? $query->paginate(31)->withQueryString() : $query->get();
        $collection = $paginate ? $records->getCollection() : $records;

        $rows = $collection->map(fn($row) => [
            'view' => [
                $row->date,
                $row->count,
                format_currency((float) $row->amount),
                format_currency((float) $row->paid),
                format_currency(max(0, (float) $row->amount - (float) $row->paid)),
            ],
            'export' => [
                $row->date,
                $row->count,
                (float) $row->amount,
                (float) $row->paid,
                max(0, (float) $row->amount - (float) $row->paid),
            ],
        ]);

        return $this->pack('Sales Analysis', ['Date', 'Invoices', 'Total Value', 'Collected', 'Outstanding'], $rows, $records, [
            'Days Analyzed' => $collection->count(),
            'Total Invoices' => $collection->sum('count'),
            'Total Revenue' => format_currency((float) $collection->sum('amount')),
            'Total Collected' => format_currency((float) $collection->sum('paid')),
        ]);
    }

    private function revenueAnalysisReport(array $filters, bool $paginate): array
    {
        $prevFrom = now()->parse($filters['from'])->subMonths(1)->startOfDay();
        $prevTo = now()->parse($filters['to'])->subMonths(1)->endOfDay();
        $currentFrom = now()->parse($filters['from'])->startOfDay();
        $currentTo = now()->parse($filters['to'])->endOfDay();

        $prevRevenue = Invoice::whereDate('date', '>=', $prevFrom)->whereDate('date', '<=', $prevTo)->sum('total');
        $currentRevenue = Invoice::whereDate('date', '>=', $currentFrom)->whereDate('date', '<=', $currentTo)->sum('total');

        $data = [
            [
                'view' => ['Previous Period', format_currency((float) $prevRevenue)],
                'export' => ['Previous Period', (float) $prevRevenue],
            ],
            [
                'view' => ['Current Period', format_currency((float) $currentRevenue)],
                'export' => ['Current Period', (float) $currentRevenue],
            ],
        ];

        if ($prevRevenue > 0) {
            $growth = (($currentRevenue - $prevRevenue) / $prevRevenue) * 100;
            $rows[] = [
                'view' => ['Growth (%)', ($growth >= 0 ? '+' : '') . number_format($growth, 2) . '%'],
                'export' => ['Growth %', $growth],
            ];
        }

        $records = collect($data);
        return $this->pack('Revenue Comparison', ['Period', 'Revenue'], $records, $records, [
            'Previous' => format_currency((float) $prevRevenue),
            'Current' => format_currency((float) $currentRevenue),
            'Change' => format_currency((float) ($currentRevenue - $prevRevenue)),
        ]);
    }

    private function receivablesReport(array $filters, bool $paginate): array
    {
        $query = Invoice::with('customer')
            ->whereDate('date', '>=', $filters['from'])
            ->whereDate('date', '<=', $filters['to'])
            ->withSum([
                'transactions as paid_total' => fn($q) => $q->where('status', 'cleared')->where('type', 'payment')->where('direction', 'income')
            ], 'amount');

        if ($filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }

        $collection = $query->get();

        $rows = $collection->filter(fn($invoice) => ((float) $invoice->total - ((float) $invoice->paid_total ?? 0)) > 0)->map(fn($invoice) => [
            'view' => [
                $invoice->date?->format(setting('date_format', 'd/m/Y')),
                $invoice->invoice_number,
                $invoice->customer?->name ?? 'N/A',
                format_currency((float) $invoice->total),
                format_currency((float) ($invoice->paid_total ?? 0)),
                format_currency((float) $invoice->total - ((float) $invoice->paid_total ?? 0)),
                $invoice->due_date ? ($invoice->due_date->isPast() ? 'OVERDUE' : 'PENDING') : 'NO DUE DATE',
            ],
            'export' => [
                $invoice->date?->toDateString(),
                $invoice->invoice_number,
                $invoice->customer?->name ?? '',
                (float) $invoice->total,
                (float) ($invoice->paid_total ?? 0),
                (float) $invoice->total - ((float) $invoice->paid_total ?? 0),
                $invoice->due_date ? ($invoice->due_date->isPast() ? 'OVERDUE' : 'PENDING') : 'NO DUE DATE',
            ],
        ])->values();

        $paginated = $rows->slice(0, 20);
        $records = collect($paginated);

        return $this->pack('Outstanding Receivables', ['Date', 'Invoice', 'Customer', 'Total', 'Paid', 'Outstanding', 'Status'], $records, $records, [
            'Pending/Overdue' => $records->count(),
            'Total Outstanding' => format_currency((float) $records->sum(fn($row) => (float) str_replace(['₹', ','], '', $row['export'][5]))),
            'Overdue' => $records->filter(fn($row) => $row['export'][6] === 'OVERDUE')->count(),
        ]);
    }

    private function paymentTrendsReport(array $filters, bool $paginate): array
    {
        $query = Transaction::where('type', 'payment')->where('direction', 'income')
            ->whereDate('transaction_date', '>=', $filters['from'])
            ->whereDate('transaction_date', '<=', $filters['to'])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total, SUM(CASE WHEN status = ? THEN amount ELSE 0 END) as cleared_amount', ['cleared'])
            ->groupBy('payment_method')
            ->orderByDesc('total');

        $records = $query->get();
        $collection = $records;

        $rows = $collection->map(fn($row) => [
            'view' => [
                ucfirst(str_replace('_', ' ', $row->payment_method)),
                $row->count,
                format_currency((float) $row->total),
                format_currency((float) $row->cleared_amount),
                ($row->total > 0 ? number_format((($row->cleared_amount / $row->total) * 100), 1) : 0) . '%',
            ],
            'export' => [
                $row->payment_method,
                $row->count,
                (float) $row->total,
                (float) $row->cleared_amount,
                ($row->total > 0 ? (($row->cleared_amount / $row->total) * 100) : 0),
            ],
        ]);

        return $this->pack('Payment Trends', ['Method', 'Count', 'Total', 'Cleared', 'Success %'], $rows, $records, [
            'Methods' => $collection->count(),
            'Total Payments' => format_currency((float) $collection->sum('total')),
            'Cleared Amount' => format_currency((float) $collection->sum('cleared_amount')),
            'Success Rate' => (float) $collection->sum('total') > 0
                ? number_format(((float) $collection->sum('cleared_amount') / (float) $collection->sum('total')) * 100, 1) . '%'
                : '0.0%',
        ]);
    }

    private function expenseAnalysisReport(array $filters, bool $paginate): array
    {
        $query = Expense::selectRaw('category, COUNT(*) as count, SUM(amount) as total, SUM(CASE WHEN status IN (?, ?) THEN amount ELSE 0 END) as paid', ['approved', 'paid'])
            ->whereDate('expense_date', '>=', $filters['from'])
            ->whereDate('expense_date', '<=', $filters['to'])
            ->groupBy('category')
            ->orderByDesc('total');

        $records = $query->get();
        $collection = $records;

        $rows = $collection->map(fn($row) => [
            'view' => [
                ucfirst($row->category ?: 'Uncategorized'),
                $row->count,
                format_currency((float) $row->total),
                format_currency((float) $row->paid),
                ($collection->sum('total') > 0 ? number_format((($row->total / $collection->sum('total')) * 100), 1) : 0) . '%',
            ],
            'export' => [
                $row->category ?: 'Uncategorized',
                $row->count,
                (float) $row->total,
                (float) $row->paid,
                ($collection->sum('total') > 0 ? (($row->total / $collection->sum('total')) * 100) : 0),
            ],
        ]);

        return $this->pack('Expense Breakdown', ['Category', 'Count', 'Amount', 'Paid', 'Percentage'], $rows, $records, [
            'Categories' => $collection->count(),
            'Total Expenses' => format_currency((float) $collection->sum('total')),
            'Paid Expenses' => format_currency((float) $collection->sum('paid')),
        ]);
    }

    private function customerPerformanceReport(array $filters, bool $paginate): array
    {
        $paymentScope = function ($q) use ($filters) {
            $q->where('status', 'cleared')->where('type', 'payment')->where('direction', 'income');
            $this->dateFilter($q, 'transaction_date', $filters);
        };

        $query = Customer::withSum(['transactions as payment_total' => $paymentScope], 'amount')
            ->withCount(['transactions as payment_count' => $paymentScope])
            ->having('payment_total', '>', 0)
            ->orderByDesc('payment_total')
            ->limit(50);

        $records = $query->get();
        $collection = $records;

        $rows = $collection->map(fn($customer) => [
            'view' => [
                $customer->name,
                $customer->company_name ?: 'N/A',
                $customer->payment_count ?? 0,
                format_currency((float) ($customer->payment_total ?? 0)),
                format_currency(((float) ($customer->payment_total ?? 0) / (float) $collection->sum('payment_total')) * 100),
            ],
            'export' => [
                $customer->name,
                $customer->company_name,
                $customer->payment_count ?? 0,
                (float) ($customer->payment_total ?? 0),
                ((float) ($customer->payment_total ?? 0) / (float) $collection->sum('payment_total')) * 100,
            ],
        ]);

        return $this->pack('Customer Performance', ['Customer', 'Company', 'Transactions', 'Total Paid', 'Share %'], $rows, $records, [
            'Top Customers' => $collection->count(),
            'Total Revenue' => format_currency((float) $collection->sum('payment_total')),
            'Avg per Customer' => format_currency((float) ($collection->sum('payment_total') / ($collection->count() ?: 1))),
        ]);
    }

    private function pack(string $title, array $headers, $rows, $records, array $summary, ?array $exportHeaders = null): array
    {
        $exportHeaders ??= $headers;
        return compact('title', 'headers', 'exportHeaders', 'rows', 'records', 'summary');
    }

    private function dateFilter(Builder $query, string $column, array $filters): void
    {
        if ($filters['from']) {
            $query->whereDate($column, '>=', $filters['from']);
        }
        if ($filters['to']) {
            $query->whereDate($column, '<=', $filters['to']);
        }
    }

    private function customerFilter(Builder $query, array $filters): void
    {
        if ($filters['customer_id']) {
            $query->where('customer_id', $filters['customer_id']);
        }
    }

    private function statusFilter(Builder $query, array $filters): void
    {
        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }
    }

    private function searchInvoices(Builder $query, array $filters): void
    {
        if (!$filters['search']) return;
        $s = $filters['search'];
        $query->where(fn($q) => $q->where('invoice_number', 'like', "%{$s}%")
            ->orWhere('subject', 'like', "%{$s}%")
            ->orWhereHas('quotation', fn($qq) => $qq->where('quotation_number', 'like', "%{$s}%"))
            ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%")->orWhere('company_name', 'like', "%{$s}%")->orWhere('mobile', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")));
    }

    private function searchQuotations(Builder $query, array $filters): void
    {
        if (!$filters['search']) return;
        $s = $filters['search'];
        $query->where(fn($q) => $q->where('quotation_number', 'like', "%{$s}%")
            ->orWhere('subject', 'like', "%{$s}%")
            ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%")->orWhere('company_name', 'like', "%{$s}%")->orWhere('mobile', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")));
    }

    private function searchPayments(Builder $query, array $filters): void
    {
        if (!$filters['search']) return;
        $s = $filters['search'];
        $query->where(fn($q) => $q->where('transaction_number', 'like', "%{$s}%")
            ->orWhere('reference_no', 'like', "%{$s}%")
            ->orWhereHas('invoice', fn($iq) => $iq->where('invoice_number', 'like', "%{$s}%"))
            ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%")->orWhere('company_name', 'like', "%{$s}%")->orWhere('mobile', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")));
    }

    private function searchExpenses(Builder $query, array $filters): void
    {
        if (!$filters['search']) return;
        $s = $filters['search'];
        $query->where(fn($q) => $q->where('expense_number', 'like', "%{$s}%")
            ->orWhere('category', 'like', "%{$s}%")
            ->orWhere('vendor_name', 'like', "%{$s}%")
            ->orWhere('reference_no', 'like', "%{$s}%")
            ->orWhereHas('invoice', fn($iq) => $iq->where('invoice_number', 'like', "%{$s}%"))
            ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%")->orWhere('company_name', 'like', "%{$s}%")->orWhere('mobile', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")));
    }

    private function sort(Builder $query, array $filters, string $dateColumn, string $amountColumn): void
    {
        match ($filters['sort']) {
            'date_asc' => $query->orderBy($dateColumn)->orderBy('id'),
            'amount_desc' => $query->orderByDesc($amountColumn)->orderByDesc($dateColumn),
            'amount_asc' => $query->orderBy($amountColumn)->orderByDesc($dateColumn),
            default => $query->orderByDesc($dateColumn)->orderByDesc('id'),
        };
    }

    private function filename(string $type, string $extension): string
    {
        return 'report-' . $type . '-' . now()->format('Ymd-His') . '.' . $extension;
    }
}
