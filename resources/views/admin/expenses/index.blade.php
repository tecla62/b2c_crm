@extends('layouts.app')
@section('title','Expenses')

@push('styles')
<style>
.exp-stat{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:18px;height:100%}
.exp-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;margin-bottom:6px}
.exp-value{font-size:22px;font-weight:850;color:#0f172a}
.exp-method{display:inline-flex;align-items:center;gap:5px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:999px;padding:3px 9px;font-size:11.5px;color:#475569;font-weight:600}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Expenses</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Expenses</li>
            </ol>
        </nav>
    </div>
    @if(auth()->user()->hasPermission('expenses.create'))
    <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>New Expense
    </a>
    @endif
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-4"><div class="exp-stat"><div class="exp-label">Invested / Tallied</div><div class="exp-value text-danger">{{ format_currency($summary['invested']) }}</div></div></div>
    <div class="col-12 col-md-4"><div class="exp-stat"><div class="exp-label">Paid Expense</div><div class="exp-value text-success">{{ format_currency($summary['paid']) }}</div></div></div>
    <div class="col-12 col-md-4"><div class="exp-stat"><div class="exp-label">Pending Expense</div><div class="exp-value text-warning">{{ format_currency($summary['pending']) }}</div></div></div>
</div>

<div class="card">
    <div class="table-toolbar card-header">
        <div class="d-flex flex-wrap gap-2 w-100">
            <input type="text" id="searchInput" class="form-control" style="max-width:240px;" placeholder="Search expense / vendor / invoice" value="{{ request('search') }}">
            <select id="statusFilter" class="form-select" style="max-width:140px;">
                <option value="">All Status</option>
                @foreach(['pending','approved','paid','cancelled'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected':'' }}>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
            <input type="date" id="fromInput" class="form-control" style="max-width:150px;" value="{{ request('from') }}">
            <input type="date" id="toInput" class="form-control" style="max-width:150px;" value="{{ request('to') }}">
            <button class="btn btn-outline-primary" onclick="applyFilters()"><i class="bi bi-funnel me-1"></i>Filter</button>
            @if(request()->hasAny(['search','status','from','to']))
            <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
            @endif
        </div>
    </div>

    <div class="table-responsive mobile-table-wrap">
        <table class="table table-hover">
            <thead>
                <tr><th>#</th><th>Expense</th><th>Customer / Invoice</th><th>Date</th><th>Method</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($expenses as $i => $expense)
                <tr>
                    <td data-label="#">{{ $expenses->firstItem() + $i }}</td>
                    <td data-label="Expense">
                        <div class="fw-700">{{ $expense->expense_number }}</div>
                        <div class="text-muted" style="font-size:11.5px;">{{ ucfirst($expense->category) }}{{ $expense->vendor_name ? ' · ' . $expense->vendor_name : '' }}</div>
                    </td>
                    <td data-label="Customer / Invoice">
                        <div class="fw-600">{{ $expense->customer?->name ?? 'General Expense' }}</div>
                        @if($expense->invoice)
                            <a href="{{ route('admin.invoices.show', $expense->invoice) }}" class="text-decoration-none" style="font-size:11.5px;">
                                <i class="bi bi-receipt me-1"></i>{{ $expense->invoice->invoice_number }}
                            </a>
                        @else
                            <span class="text-muted" style="font-size:11.5px;">No invoice linked</span>
                        @endif
                    </td>
                    <td data-label="Date">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td data-label="Method"><span class="exp-method"><i class="bi bi-wallet2"></i>{{ str_replace('_',' ', ucfirst($expense->payment_method)) }}</span></td>
                    <td data-label="Amount"><span class="fw-800 text-danger">-{{ format_currency($expense->amount) }}</span></td>
                    <td data-label="Status" id="status-badge-{{ $expense->id }}">{!! $expense->status_badge !!}</td>
                    <td data-label="Actions">
                        <div class="action-btns">
                            <a href="{{ route('admin.expenses.show', $expense) }}" class="act-btn act-view" title="View"><i class="bi bi-eye"></i></a>
                            @if(auth()->user()->hasPermission('expenses.edit'))
                            <a href="{{ route('admin.expenses.edit', $expense) }}" class="act-btn act-edit" title="Edit"><i class="bi bi-pencil"></i></a>
                            <div class="dropdown d-inline-block">
                                <button class="act-btn" title="Change Status" data-bs-toggle="dropdown" style="background:#f0f4ff;color:var(--primary,#4f46e5);"><i class="bi bi-arrow-repeat"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end" style="min-width:140px;font-size:13px;">
                                    @foreach(['pending','approved','paid','cancelled'] as $s)
                                    <li><a class="dropdown-item {{ $expense->status === $s ? 'fw-700 text-primary' : '' }}" href="#" onclick="changeStatus({{ $expense->id }}, '{{ $s }}');return false;">{{ $expense->status === $s ? '✓ ' : '' }}{{ ucfirst($s) }}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            @if(auth()->user()->hasPermission('expenses.delete'))
                            <button onclick="deleteExpense({{ $expense->id }})" class="act-btn act-delete" title="Delete"><i class="bi bi-trash"></i></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-5"><i class="bi bi-wallet2 fs-2 d-block mb-2"></i>No expenses found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$expenses" label="expenses" />
</div>
@endsection

@push('scripts')
<script>
function applyFilters() {
    const params = new URLSearchParams({ search: $('#searchInput').val(), status: $('#statusFilter').val(), from: $('#fromInput').val(), to: $('#toInput').val() });
    window.location.href = `{{ route('admin.expenses.index') }}?${params.toString()}`;
}
$('#searchInput').on('keypress', e => { if (e.which === 13) applyFilters(); });

const expenseStatusUrl = id => '{{ route("admin.expenses.status", ["expense" => "__EXP__"]) }}'.replace('__EXP__', id);
const expenseDeleteUrl = id => '{{ route("admin.expenses.destroy", ["expense" => "__EXP__"]) }}'.replace('__EXP__', id);

function changeStatus(id, status) {
    $.post(expenseStatusUrl(id), { status }, r => {
        document.getElementById(`status-badge-${id}`).innerHTML = r.badge;
        Swal.fire({ icon:'success', text:r.message, timer:1500, showConfirmButton:false });
    }).fail(x => Swal.fire('Error', x.responseJSON?.message || 'Error', 'error'));
}
function deleteExpense(id) {
    Swal.fire({ title:'Delete this expense?', icon:'warning', showCancelButton:true, confirmButtonColor:'#ef4444', confirmButtonText:'Yes, delete!' })
    .then(r => {
        if (!r.isConfirmed) return;
        $.ajax({ url: expenseDeleteUrl(id), type:'DELETE', success: r => Swal.fire('Deleted!', r.message,'success').then(()=>location.reload()), error: x => Swal.fire('Error!', x.responseJSON?.message || 'Error','error') });
    });
}
</script>
@endpush
