<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('customer', 'invoice', 'createdBy');

        if ($s = $request->search) {
            $query->where(function ($q) use ($s) {
                $q->where('expense_number', 'like', "%{$s}%")
                    ->orWhere('vendor_name', 'like', "%{$s}%")
                    ->orWhere('reference_no', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%"))
                    ->orWhereHas('invoice', fn($iq) => $iq->where('invoice_number', 'like', "%{$s}%"));
            });
        }

        foreach (['status', 'category', 'payment_method'] as $filter) {
            if ($value = $request->{$filter}) {
                $query->where($filter, $value);
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('expense_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('expense_date', '<=', $request->to);
        }

        $summary = [
            'paid' => (clone $query)->where('status', 'paid')->sum('amount'),
            'approved' => (clone $query)->where('status', 'approved')->sum('amount'),
            'pending' => (clone $query)->where('status', 'pending')->sum('amount'),
        ];
        $summary['invested'] = $summary['paid'] + $summary['approved'];

        $expenses = $query->latest('expense_date')->latest()->paginate(12)->withQueryString();

        return view('admin.expenses.index', compact('expenses', 'summary'));
    }

    public function create(Request $request)
    {
        $expense = null;
        $customers = Customer::orderBy('name')->get();
        $invoices = Invoice::with('customer')->latest()->get();

        $selectedTransaction = $request->filled('transaction_id')
            ? Transaction::with('invoice', 'customer')->find($request->transaction_id)
            : null;
        $selectedInvoice = $request->filled('invoice_id')
            ? Invoice::with('customer')->find($request->invoice_id)
            : $selectedTransaction?->invoice;

        return view('admin.expenses.form', compact('expense', 'customers', 'invoices', 'selectedTransaction', 'selectedInvoice'));
    }

    public function store(Request $request)
    {
        $data = $this->validateExpense($request);
        $data['transaction_id'] = null;
        $data['expense_number'] = Expense::generateNumber();
        $data['created_by'] = auth()->id();

        $expense = Expense::create($data);
        ActivityLog::log('CREATE', 'Expenses', "Created expense: {$expense->expense_number}", $expense);

        return redirect()->route('admin.expenses.show', $expense)
            ->with('success', "Expense {$expense->expense_number} created.");
    }

    public function show(Expense $expense)
    {
        $expense->load('customer', 'invoice.customer', 'createdBy');
        return view('admin.expenses.show', compact('expense'));
    }

    public function edit(Expense $expense)
    {
        $customers = Customer::orderBy('name')->get();
        $invoices = Invoice::with('customer')->latest()->get();
        $selectedTransaction = $expense->transaction;
        $selectedInvoice = $expense->invoice;

        return view('admin.expenses.form', compact('expense', 'customers', 'invoices', 'selectedTransaction', 'selectedInvoice'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $this->validateExpense($request);
        $data['transaction_id'] = null;
        $expense->update($data);
        ActivityLog::log('UPDATE', 'Expenses', "Updated expense: {$expense->expense_number}", $expense);

        return redirect()->route('admin.expenses.show', $expense)
            ->with('success', 'Expense updated successfully.');
    }

    public function updateStatus(Request $request, Expense $expense)
    {
        $request->validate(['status' => 'required|in:pending,approved,paid,cancelled']);
        $old = $expense->status;
        $expense->update(['status' => $request->status]);
        ActivityLog::log('STATUS_CHANGE', 'Expenses', "Status: {$old} -> {$request->status}: {$expense->expense_number}", $expense);

        return response()->json([
            'success' => true,
            'message' => 'Expense status updated.',
            'badge'   => $expense->fresh()->status_badge,
        ]);
    }

    public function destroy(Expense $expense)
    {
        ActivityLog::log('DELETE', 'Expenses', "Deleted expense: {$expense->expense_number}", $expense);
        $expense->delete();
        return response()->json(['success' => true, 'message' => 'Expense deleted.']);
    }

    private function validateExpense(Request $request): array
    {
        return $request->validate([
            'invoice_id'     => 'nullable|exists:invoices,id',
            'transaction_id' => 'nullable|exists:transactions,id',
            'customer_id'    => 'nullable|exists:customers,id',
            'expense_date'   => 'required|date',
            'category'       => 'required|string|max:80',
            'vendor_name'    => 'nullable|string|max:160',
            'amount'         => 'required|numeric|min:0.01|max:999999999.99',
            'payment_method' => ['required', Rule::in(['cash', 'bank_transfer', 'cheque', 'card', 'upi', 'other'])],
            'reference_no'   => 'nullable|string|max:120',
            'status'         => ['required', Rule::in(['pending', 'approved', 'paid', 'cancelled'])],
            'notes'          => 'nullable|string',
        ]);
    }
}
