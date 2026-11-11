<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AssetAssignment;
use App\Models\AssetItem;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\LoginHistory;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        if (!in_array($period, ['month', 'quarter', 'year', 'all'], true)) {
            $period = 'month';
        }

        [$from, $to, $periodLabel] = $this->resolvePeriod($period);

        $dateScope = function ($query, string $column) use ($from, $to) {
            if ($from) {
                $query->whereDate($column, '>=', $from);
            }

            if ($to) {
                $query->whereDate($column, '<=', $to);
            }
        };

        $invoiceQuery = Invoice::query();
        $dateScope($invoiceQuery, 'date');

        $quotationQuery = Quotation::query();
        $dateScope($quotationQuery, 'date');

        $paymentQuery = Transaction::query()
            ->where('type', 'payment')
            ->where('direction', 'income');
        $dateScope($paymentQuery, 'transaction_date');

        $expenseQuery = Expense::query();
        $dateScope($expenseQuery, 'expense_date');

        $paidInvoiceIds = Transaction::where('type', 'payment')
            ->where('direction', 'income')
            ->where('status', 'cleared')
            ->whereNotNull('invoice_id')
            ->groupBy('invoice_id')
            ->selectRaw('invoice_id, SUM(amount) as paid_total')
            ->pluck('paid_total', 'invoice_id');

        $outstandingInvoices = Invoice::with('customer')
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->latest('due_date')
            ->latest()
            ->take(8)
            ->get()
            ->map(function ($invoice) use ($paidInvoiceIds) {
                $paid = (float) ($paidInvoiceIds[$invoice->id] ?? 0);
                $invoice->paid_amount = $paid;
                $invoice->outstanding_amount = max(0, (float) $invoice->total - $paid);

                return $invoice;
            })
            ->filter(fn($invoice) => $invoice->outstanding_amount > 0)
            ->values();

        $totalInvoiceValue = (clone $invoiceQuery)->where('status', '!=', 'cancelled')->sum('total');
        $totalCollected = (clone $paymentQuery)->where('status', 'cleared')->sum('amount');
        $totalExpenses = (clone $expenseQuery)->whereIn('status', ['approved', 'paid'])->sum('amount');

        $stats = [
            'customers' => Customer::count(),
            'active_customers' => Customer::where('status', 'active')->count(),
            'quotations' => (clone $quotationQuery)->count(),
            'quotation_value' => (clone $quotationQuery)->whereNotIn('status', ['rejected', 'expired'])->sum('total'),
            'approved_quotations' => (clone $quotationQuery)->whereIn('status', ['accepted', 'approved'])->count(),
            'invoices' => (clone $invoiceQuery)->count(),
            'invoice_value' => $totalInvoiceValue,
            'collected' => $totalCollected,
            'expenses' => $totalExpenses,
            'net_balance' => $totalCollected - $totalExpenses,
            'outstanding' => max(0, Invoice::where('status', '!=', 'cancelled')->sum('total') - $paidInvoiceIds->sum()),
            'pending_payments' => (clone $paymentQuery)->where('status', 'pending')->sum('amount'),
            'assets_available' => AssetItem::get()->sum(fn($asset) => $asset->available_quantity),
            'assets_issued' => AssetItem::sum('issued_quantity'),
            'open_assignments' => AssetAssignment::whereIn('status', ['issued', 'partial_returned'])
                ->distinct('assignment_group_code')
                ->count('assignment_group_code'),
            'services' => Service::where('status', 'active')->count(),
            'users' => User::count(),
            'active_users' => User::where('status', 'active')->count(),
        ];

        $recentUsers = User::with('role')->latest()->take(6)->get();
        $recentCustomers = Customer::with('assignedTo')->latest()->take(6)->get();
        $recentQuotations = Quotation::with('customer')->latest()->take(6)->get();
        $recentInvoices = Invoice::with('customer')->latest()->take(6)->get();
        $recentPayments = Transaction::with('customer', 'invoice')
            ->where('type', 'payment')
            ->where('direction', 'income')
            ->latest('transaction_date')
            ->latest()
            ->take(6)
            ->get();
        $recentExpenses = Expense::with('customer', 'invoice')->latest('expense_date')->latest()->take(6)->get();
        $recentActivities = ActivityLog::with('user')->latest()->take(10)->get();
        $recentLogins = LoginHistory::with('user')->latest()->take(6)->get();
        $recentAssetMovements = \App\Models\AssetMovement::with('assetItem', 'employee', 'customer')
            ->whereIn('type', ['issue', 'return'])
            ->latest('movement_date')
            ->latest()
            ->take(6)
            ->get();

        $lowStockAssets = AssetItem::where('status', 'active')
            ->get()
            ->filter(fn($asset) => $asset->available_quantity <= $asset->minimum_quantity)
            ->sortBy('available_quantity')
            ->take(6)
            ->values();

        $openAssignments = AssetAssignment::with('assetItem', 'employee', 'customer')
            ->whereIn('status', ['issued', 'partial_returned'])
            ->latest('issue_date')
            ->latest()
            ->take(6)
            ->get();

        $topCustomers = Customer::query()
            ->withSum(['transactions as payment_total' => fn($q) => $q
                ->where('status', 'cleared')
                ->where('type', 'payment')
                ->where('direction', 'income')
            ], 'amount')
            ->withSum(['expenses as expense_total' => fn($q) => $q
                ->whereIn('status', ['approved', 'paid'])
            ], 'amount')
            ->orderByRaw('COALESCE(payment_total, 0) DESC')
            ->take(5)
            ->get();

        $pipeline = [
            'quotations' => [
                'draft' => Quotation::where('status', 'draft')->count(),
                'sent' => Quotation::where('status', 'sent')->count(),
                'approved' => Quotation::whereIn('status', ['accepted', 'approved'])->count(),
                'rejected' => Quotation::whereIn('status', ['rejected', 'expired'])->count(),
            ],
            'invoices' => [
                'draft' => Invoice::where('status', 'draft')->count(),
                'sent' => Invoice::where('status', 'sent')->count(),
                'partial' => Invoice::where('status', 'partial')->count(),
                'paid' => Invoice::where('status', 'paid')->count(),
                'overdue' => Invoice::where('status', 'overdue')->count(),
            ],
            'payments' => [
                'pending' => Transaction::where('type', 'payment')->where('direction', 'income')->where('status', 'pending')->count(),
                'cleared' => Transaction::where('type', 'payment')->where('direction', 'income')->where('status', 'cleared')->count(),
                'failed' => Transaction::where('type', 'payment')->where('direction', 'income')->whereIn('status', ['failed', 'cancelled'])->count(),
            ],
        ];

        $monthlyTrend = collect(range(5, 0))->map(function ($i) {
            $month = now()->subMonths($i);

            return [
                'month' => $month->format('M Y'),
                'invoices' => (float) Invoice::whereYear('date', $month->year)
                    ->whereMonth('date', $month->month)
                    ->where('status', '!=', 'cancelled')
                    ->sum('total'),
                'collections' => (float) Transaction::whereYear('transaction_date', $month->year)
                    ->whereMonth('transaction_date', $month->month)
                    ->where('type', 'payment')
                    ->where('direction', 'income')
                    ->where('status', 'cleared')
                    ->sum('amount'),
                'expenses' => (float) Expense::whereYear('expense_date', $month->year)
                    ->whereMonth('expense_date', $month->month)
                    ->whereIn('status', ['approved', 'paid'])
                    ->sum('amount'),
            ];
        });

        return view('admin.dashboard', compact(
            'period',
            'periodLabel',
            'stats',
            'recentUsers',
            'recentCustomers',
            'recentQuotations',
            'recentInvoices',
            'recentPayments',
            'recentExpenses',
            'recentActivities',
            'recentLogins',
            'recentAssetMovements',
            'lowStockAssets',
            'openAssignments',
            'outstandingInvoices',
            'topCustomers',
            'pipeline',
            'monthlyTrend'
        ));
    }

    private function resolvePeriod(string $period): array
    {
        return match ($period) {
            'quarter' => [now()->startOfQuarter()->toDateString(), now()->endOfQuarter()->toDateString(), 'This Quarter'],
            'year' => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString(), 'This Year'],
            'all' => [null, null, 'All Time'],
            default => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString(), 'This Month'],
        };
    }
}
