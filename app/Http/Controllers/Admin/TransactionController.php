<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\PaymentReceiptMail;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Setting;
use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('customer', 'invoice', 'createdBy');

        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('transaction_number', 'like', "%{$s}%")
                    ->orWhere('reference_no', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('invoice', fn($iq) => $iq->where('invoice_number', 'like', "%{$s}%"));
            });
        }

        $query->where('type', 'payment')->where('direction', 'income');

        foreach (['status', 'payment_method'] as $filter) {
            if ($value = $request->{$filter}) {
                $query->where($filter, $value);
            }
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('transaction_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('transaction_date', '<=', $request->to);
        }

        $stateCounts = [
            'all' => (clone $query)->count(),
            'partial' => (clone $query)->whereHas('invoice', fn($q) => $q->where('status', 'partial'))->count(),
            'paid' => (clone $query)->whereHas('invoice', fn($q) => $q->where('status', 'paid'))->count(),
        ];

        if ($request->payment_state === 'partial') {
            $query->whereHas('invoice', fn($q) => $q->where('status', 'partial'));
        } elseif ($request->payment_state === 'paid') {
            $query->whereHas('invoice', fn($q) => $q->where('status', 'paid'));
        }

        $summary = [];
        $summary['paid'] = (clone $query)->where('status', 'cleared')->sum('amount');
        $summary['pending'] = (clone $query)->where('status', 'pending')->sum('amount');
        $summary['invoice_total'] = Invoice::whereIn('id', (clone $query)->whereNotNull('invoice_id')->pluck('invoice_id'))->sum('total');
        $summary['outstanding'] = max(0, $summary['invoice_total'] - $summary['paid']);

        $transactions = $query->latest('transaction_date')->latest()->paginate(12)->withQueryString();
        $invoiceBalances = $this->invoiceBalances($transactions->getCollection()->pluck('invoice')->filter());
        $customers = Customer::orderBy('name')->get();

        return view('admin.transactions.index', compact('transactions', 'summary', 'invoiceBalances', 'stateCounts', 'customers'));
    }

    public function customerLedger(Request $request)
    {
        $paymentScope = function ($q) use ($request) {
            $q->where('status', 'cleared')
                ->where('direction', 'income')
                ->where('type', 'payment');

            if ($request->filled('from')) {
                $q->whereDate('transaction_date', '>=', $request->from);
            }

            if ($request->filled('to')) {
                $q->whereDate('transaction_date', '<=', $request->to);
            }
        };

        $expenseScope = function ($q) use ($request) {
            $q->whereIn('status', ['approved', 'paid']);

            if ($request->filled('from')) {
                $q->whereDate('expense_date', '>=', $request->from);
            }

            if ($request->filled('to')) {
                $q->whereDate('expense_date', '<=', $request->to);
            }
        };

        $customersQuery = Customer::query()
            ->withSum(['transactions as payment_total' => $paymentScope], 'amount')
            ->withCount(['transactions as payment_count' => $paymentScope])
            ->withSum(['expenses as expense_total' => $expenseScope], 'amount')
            ->withCount(['expenses as expense_count' => $expenseScope]);

        if ($request->filled('customer_id')) {
            $customersQuery->where('id', $request->customer_id);
        }

        if ($s = $request->search) {
            $customersQuery->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('company_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('mobile', 'like', "%{$s}%");
            });
        }

        if (!$request->boolean('include_all')) {
            $customersQuery->where(function ($q) use ($paymentScope, $expenseScope) {
                $q->whereHas('transactions', $paymentScope)
                    ->orWhereHas('expenses', $expenseScope);
            });
        }

        $summaryCustomers = (clone $customersQuery)->get();
        $summary = [
            'customers' => $summaryCustomers->count(),
            'payments' => (float) $summaryCustomers->sum(fn($customer) => $customer->payment_total ?? 0),
            'expenses' => (float) $summaryCustomers->sum(fn($customer) => $customer->expense_total ?? 0),
        ];
        $summary['net'] = $summary['payments'] - $summary['expenses'];

        $sort = $request->get('sort', 'net');
        $customers = $customersQuery
            ->orderByRaw(match ($sort) {
                'payments' => 'COALESCE(payment_total, 0) DESC',
                'expenses' => 'COALESCE(expense_total, 0) DESC',
                default => '(COALESCE(payment_total, 0) - COALESCE(expense_total, 0)) DESC',
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $selectedCustomer = $request->filled('customer_id')
            ? Customer::find($request->customer_id)
            : null;

        $ledgerEntries = collect();
        if ($selectedCustomer) {
            $payments = Transaction::with('invoice')
                ->where('customer_id', $selectedCustomer->id)
                ->tap($paymentScope)
                ->get()
                ->map(fn($payment) => [
                    'date' => $payment->transaction_date,
                    'number' => $payment->transaction_number,
                    'type' => 'Payment',
                    'invoice' => $payment->invoice,
                    'method' => $payment->payment_method,
                    'amount' => (float) $payment->amount,
                    'url' => route('admin.transactions.show', $payment),
                ]);

            $expenses = Expense::with('invoice')
                ->where('customer_id', $selectedCustomer->id)
                ->tap($expenseScope)
                ->get()
                ->map(fn($expense) => [
                    'date' => $expense->expense_date,
                    'number' => $expense->expense_number,
                    'type' => 'Expense',
                    'invoice' => $expense->invoice,
                    'method' => $expense->payment_method,
                    'amount' => -(float) $expense->amount,
                    'url' => route('admin.expenses.show', $expense),
                ]);

            $ledgerEntries = $payments
                ->merge($expenses)
                ->sortByDesc('date')
                ->values()
                ->take(30);
        }

        $customerOptions = Customer::orderBy('name')->get(['id', 'name', 'company_name']);

        return view('admin.transactions.customer-ledger', compact(
            'customers',
            'summary',
            'selectedCustomer',
            'ledgerEntries',
            'customerOptions'
        ));
    }

    public function create(Request $request)
    {
        $transaction = null;
        $customers = Customer::orderBy('name')->get();
        $invoices = Invoice::with('customer')
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->limit(100)
            ->get();

        $selectedInvoice = $request->filled('invoice_id')
            ? Invoice::with('customer')->find($request->invoice_id)
            : null;

        if ($selectedInvoice && !$invoices->contains('id', $selectedInvoice->id)) {
            $invoices->prepend($selectedInvoice);
        }

        $invoiceBalances = $this->invoiceBalances($invoices);
        $invoices = $invoices->filter(function ($invoice) use ($invoiceBalances) {
            $balance = $invoiceBalances[$invoice->id] ?? ['outstanding' => $invoice->total];

            return ($balance['outstanding'] ?? 0) > 0.01;
        })->values();

        if ($selectedInvoice && !$invoices->contains('id', $selectedInvoice->id)) {
            $selectedInvoice = null;
        }

        return view('admin.transactions.form', compact('transaction', 'customers', 'invoices', 'selectedInvoice', 'invoiceBalances'));
    }

    public function store(Request $request)
    {
        $data = $this->validateTransaction($request);
        $data['transaction_number'] = Transaction::generateNumber();
        $data['created_by'] = auth()->id();
        $data['type'] = 'payment';
        $data['direction'] = 'income';

        $transaction = Transaction::create($data);
        $this->syncInvoiceStatus($transaction->invoice);

        ActivityLog::log('CREATE', 'Payments', "Recorded payment: {$transaction->transaction_number}", $transaction);

        return redirect()->route('admin.transactions.show', $transaction)
            ->with('success', "Payment {$transaction->transaction_number} recorded.");
    }

    public function show(Transaction $transaction)
    {
        $transaction->load('customer', 'invoice.customer', 'createdBy', 'expenses');
        $invoicePayments = collect();
        $accountSummary = null;

        if ($transaction->invoice_id) {
            $invoicePayments = Transaction::where('invoice_id', $transaction->invoice_id)
                ->where('status', 'cleared')
                ->where('type', 'payment')
                ->latest('transaction_date')
                ->get();
            $accountSummary = $this->invoiceAccountSummary($transaction->invoice);
        }

        return view('admin.transactions.show', compact('transaction', 'invoicePayments', 'accountSummary'));
    }

    public function edit(Transaction $transaction)
    {
        $customers = Customer::orderBy('name')->get();
        $invoices = Invoice::with('customer')
            ->where('status', '!=', 'cancelled')
            ->latest()
            ->limit(100)
            ->get();
        $selectedInvoice = $transaction->invoice;
        $invoiceBalances = $this->invoiceBalances($invoices);

        if ($selectedInvoice && !$invoices->contains('id', $selectedInvoice->id)) {
            $invoices->prepend($selectedInvoice->loadMissing('customer'));
            $invoiceBalances = $this->invoiceBalances($invoices);
        }

        $invoices = $invoices->filter(function ($invoice) use ($invoiceBalances, $selectedInvoice) {
            $balance = $invoiceBalances[$invoice->id] ?? ['outstanding' => $invoice->total];

            return ($balance['outstanding'] ?? 0) > 0.01
                || ($selectedInvoice && $selectedInvoice->id === $invoice->id);
        })->values();

        return view('admin.transactions.form', compact('transaction', 'customers', 'invoices', 'selectedInvoice', 'invoiceBalances'));
    }

    public function update(Request $request, Transaction $transaction)
    {
        $oldInvoice = $transaction->invoice;
        $data = $this->validateTransaction($request);
        $data['type'] = 'payment';
        $data['direction'] = 'income';
        $transaction->update($data);

        $this->syncInvoiceStatus($oldInvoice);
        $this->syncInvoiceStatus($transaction->fresh()->invoice);

        ActivityLog::log('UPDATE', 'Payments', "Updated payment: {$transaction->transaction_number}", $transaction);

        return redirect()->route('admin.transactions.show', $transaction)
            ->with('success', 'Payment updated successfully.');
    }

    public function updateStatus(Request $request, Transaction $transaction)
    {
        $request->validate(['status' => 'required|in:pending,cleared,failed,cancelled']);
        $old = $transaction->status;
        $transaction->update(['status' => $request->status]);
        $this->syncInvoiceStatus($transaction->invoice);

        ActivityLog::log('STATUS_CHANGE', 'Payments', "Status: {$old} -> {$request->status}: {$transaction->transaction_number}", $transaction);

        return response()->json([
            'success' => true,
            'message' => 'Payment status updated.',
            'badge'   => $transaction->fresh()->status_badge,
        ]);
    }

    public function destroy(Transaction $transaction)
    {
        $invoice = $transaction->invoice;
        ActivityLog::log('DELETE', 'Payments', "Deleted payment: {$transaction->transaction_number}", $transaction);
        $transaction->delete();
        $this->syncInvoiceStatus($invoice);

        return response()->json(['success' => true, 'message' => 'Payment deleted.']);
    }

    public function receipt(Transaction $transaction)
    {
        $transaction->load('customer', 'invoice.customer', 'invoice.quotation', 'createdBy');
        $accountSummary = $this->invoiceAccountSummary($transaction->invoice);

        if (request()->query('download')) {
            $pdfMode = true;
            $pdf = Pdf::loadView('admin.transactions.receipt', compact('transaction', 'accountSummary', 'pdfMode'))
                ->setPaper('a4', 'portrait');

            return $pdf->download($transaction->transaction_number . '-receipt.pdf');
        }

        return view('admin.transactions.receipt', compact('transaction', 'accountSummary'));
    }

    public function sendReceipt(Request $request, Transaction $transaction)
    {
        $transaction->load('customer', 'invoice.customer', 'createdBy');

        $request->validate([
            'to' => 'nullable|email',
        ]);

        $to = $request->to ?: $transaction->customer?->email;

        if (!$to) {
            return response()->json([
                'success' => false,
                'message' => 'Customer email is missing. Add an email or enter one manually.',
            ], 422);
        }

        try {
            $this->applySmtpSettings();
            Mail::to($to)->send(new PaymentReceiptMail($transaction));
        } catch (\Throwable $e) {
            \Log::error('Payment receipt email failed: ' . $e->getMessage(), [
                'transaction_id' => $transaction->id,
                'to' => $to,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send receipt. Check SMTP settings. ' . $e->getMessage(),
            ], 500);
        }

        $transaction->update([
            'receipt_sent_at' => now(),
            'receipt_sent_to' => $to,
        ]);

        ActivityLog::log('EMAIL', 'Transactions', "Sent payment receipt {$transaction->transaction_number} to {$to}", $transaction);

        return response()->json([
            'success' => true,
            'message' => 'Payment receipt sent to ' . $to,
            'sent_at' => now()->format('d M Y, h:i A'),
        ]);
    }

    private function validateTransaction(Request $request): array
    {
        if (!$request->filled('customer_id') && $request->filled('invoice_id')) {
            $invoice = Invoice::find($request->invoice_id);
            if ($invoice) {
                $request->merge(['customer_id' => $invoice->customer_id]);
            }
        }

        return $request->validate([
            'invoice_id'       => 'required|exists:invoices,id',
            'customer_id'      => 'required|exists:customers,id',
            'transaction_date' => 'required|date',
            'amount'           => 'required|numeric|min:0.01|max:999999999.99',
            'payment_method'   => ['required', Rule::in(['cash', 'bank_transfer', 'cheque', 'card', 'upi', 'other'])],
            'reference_no'     => 'nullable|string|max:120',
            'status'           => ['required', Rule::in(['pending', 'cleared', 'failed', 'cancelled'])],
            'notes'            => 'nullable|string',
        ]);
    }

    private function syncInvoiceStatus(?Invoice $invoice): void
    {
        if (!$invoice || $invoice->status === 'cancelled') {
            return;
        }

        $paid = Transaction::where('invoice_id', $invoice->id)
            ->where('status', 'cleared')
            ->where('direction', 'income')
            ->where('type', 'payment')
            ->sum('amount');

        if ($paid <= 0) {
            $status = 'sent';
        } elseif ($paid + 0.01 >= $invoice->total) {
            $status = 'paid';
        } else {
            $status = 'partial';
        }

        $invoice->update(['status' => $status]);
    }

    private function invoiceBalances($invoices): array
    {
        $ids = $invoices->pluck('id');
        $paid = Transaction::whereIn('invoice_id', $ids)
            ->where('status', 'cleared')
            ->where('direction', 'income')
            ->where('type', 'payment')
            ->groupBy('invoice_id')
            ->selectRaw('invoice_id, SUM(amount) as total')
            ->pluck('total', 'invoice_id');

        return $invoices->mapWithKeys(function ($invoice) use ($paid) {
            $paidAmount = (float) ($paid[$invoice->id] ?? 0);
            return [$invoice->id => [
                'paid' => $paidAmount,
                'outstanding' => max(0, $invoice->total - $paidAmount),
            ]];
        })->toArray();
    }

    private function invoiceAccountSummary(?Invoice $invoice): ?array
    {
        if (!$invoice) {
            return null;
        }

        $paid = Transaction::where('invoice_id', $invoice->id)
            ->where('status', 'cleared')
            ->where('direction', 'income')
            ->where('type', 'payment')
            ->sum('amount');

        $expense = Expense::where('invoice_id', $invoice->id)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');

        return [
            'invoice_total' => (float) $invoice->total,
            'paid' => (float) $paid,
            'outstanding' => max(0, (float) $invoice->total - (float) $paid),
            'expense' => (float) $expense,
            'net_balance' => (float) $paid - (float) $expense,
        ];
    }

    private function applySmtpSettings(): void
    {
        Config::set('mail.mailers.smtp.host',       Setting::get('mail_host'));
        Config::set('mail.mailers.smtp.port',       Setting::get('mail_port'));
        Config::set('mail.mailers.smtp.username',   Setting::get('mail_username'));
        Config::set('mail.mailers.smtp.password',   Setting::get('mail_password'));
        Config::set('mail.mailers.smtp.encryption', Setting::get('mail_encryption'));
        Config::set('mail.from.address',            Setting::get('mail_from_address'));
        Config::set('mail.from.name',               Setting::get('mail_from_name', setting('company_name', config('app.name'))));
    }
}
