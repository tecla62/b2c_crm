@extends('layouts.app')
@section('title', 'Weekly & Monthly Payrolls')

@push('styles')
<style>
.pay-stat {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    padding: 16px 20px;
    height: 100%;
}
.pay-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #94a3b8;
    margin-bottom: 4px;
}
.pay-value {
    font-size: 24px;
    font-weight: 850;
    color: #0f172a;
}
.pay-icon {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title">Weekly & Period Payrolls</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Staff</a></li>
                <li class="breadcrumb-item active">Payrolls</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-clock-history me-1"></i>View Attendance & OT
        </a>
        @if(auth()->user()->hasPermission('payroll.create'))
        <a href="{{ route('admin.payrolls.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Process Weekly / Period Salary
        </a>
        @endif
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="pay-stat">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="pay-label">Total Paid Disbursed</div>
                    <div class="pay-value text-success">{{ format_currency($summary['total_disbursed']) }}</div>
                </div>
                <div class="pay-icon" style="background:#dcfce7;color:#15803d;">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pay-stat">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="pay-label">Approved & Pending</div>
                    <div class="pay-value text-primary">{{ format_currency($summary['approved_pending']) }}</div>
                </div>
                <div class="pay-icon" style="background:#e0e7ff;color:#4338ca;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pay-stat">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="pay-label">Total Extra Duty Paid</div>
                    <div class="pay-value text-info">{{ format_currency($summary['total_extra_duty']) }}</div>
                </div>
                <div class="pay-icon" style="background:#e0f2fe;color:#0284c7;">
                    <i class="bi bi-lightning-charge"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="pay-stat">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="pay-label">Draft Wages</div>
                    <div class="pay-value text-warning">{{ $summary['draft_count'] }}</div>
                </div>
                <div class="pay-icon" style="background:#fef3c7;color:#b45309;">
                    <i class="bi bi-file-earmark-text"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Filters Toolbar --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.payrolls.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search payroll # or staff..." value="{{ request('search') }}">
            </div>
            <div class="col-6 col-md-3">
                <select name="employee_id" class="form-select">
                    <option value="">All Employees</option>
                    @foreach($employees as $e)
                    <option value="{{ $e->id }}" {{ request('employee_id') == $e->id ? 'selected' : '' }}>
                        {{ $e->name }} ({{ $e->employee_code }})
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="period_type" class="form-select">
                    <option value="">All Periods</option>
                    <option value="weekly" {{ request('period_type') == 'weekly' ? 'selected' : '' }}>Weekly Wages</option>
                    <option value="monthly" {{ request('period_type') == 'monthly' ? 'selected' : '' }}>Monthly Salary</option>
                    <option value="daily_ondemand" {{ request('period_type') == 'daily_ondemand' ? 'selected' : '' }}>Daily / Flexible</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-6 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                @if(request()->hasAny(['search', 'employee_id', 'period_type', 'status']))
                <a href="{{ route('admin.payrolls.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Payroll Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-cash-stack text-success me-2"></i>Generated Payroll Slips</h5>
        <span class="badge bg-light text-dark border">{{ $payrolls->total() }} records</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Payroll #</th>
                    <th>Employee / Staff</th>
                    <th>Period</th>
                    <th>Days Worked</th>
                    <th>Base Earned</th>
                    <th>Extra Duty (OT)</th>
                    <th>Net Payable</th>
                    <th>Status</th>
                    <th style="width: 170px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payrolls as $p)
                <tr>
                    <td>
                        <a href="{{ route('admin.payrolls.show', $p) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                            {{ $p->payroll_number }}
                        </a>
                        <small class="d-block text-muted">{{ $p->created_at->format('d/m/Y') }}</small>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $p->employee?->name ?? 'Staff' }}</div>
                        <small class="text-muted">{{ $p->employee?->employee_code }} &bull; {{ ucfirst($p->period_type) }}</small>
                    </td>
                    <td>
                        <div class="small fw-semibold">
                            {{ $p->start_date->format('d M') }} &mdash; {{ $p->end_date->format('d M Y') }}
                        </div>
                        <small class="text-muted">
                            @if($p->period_type === 'weekly') 6-Day Weekly Cycle
                            @else {{ $p->period_type }}
                            @endif
                        </small>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border fw-bold">{{ $p->total_working_days }} days</span>
                        <div class="text-muted" style="font-size:11px;">
                            {{ $p->days_present }} P @if($p->days_half_day > 0) + {{ $p->days_half_day }} HD @endif
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ format_currency($p->base_salary_earned) }}</div>
                    </td>
                    <td>
                        @if($p->total_extra_duty_amount > 0)
                        <div class="fw-bold text-success">{{ format_currency($p->total_extra_duty_amount) }}</div>
                        <small class="text-muted">{{ $p->total_extra_duty_hours }} hrs OT</small>
                        @else
                        <span class="text-muted small">&mdash;</span>
                        @endif
                    </td>
                    <td>
                        <div class="fw-bold text-dark fs-6">{{ format_currency($p->net_amount) }}</div>
                    </td>
                    <td>
                        {!! $p->status_badge !!}
                        @if($p->status === 'paid' && $p->payment_date)
                        <small class="d-block text-muted" style="font-size:11px;">
                            {{ $p->payment_method ?: 'Cash' }} &bull; {{ $p->payment_date->format('d/m/Y') }}
                        </small>
                        @endif
                    </td>
                    <td data-label="Actions">
                        <div class="action-btns">
                            {{-- View Payslip --}}
                            <a href="{{ route('admin.payrolls.show', $p) }}" class="act-btn act-view" title="View Payslip">
                                <i class="bi bi-eye"></i>
                            </a>

                            {{-- PDF Download --}}
                            <a href="{{ route('admin.payrolls.pdf', $p) }}" class="act-btn act-pdf" title="Download PDF">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>

                            {{-- Print --}}
                            <a href="{{ route('admin.payrolls.print', $p) }}" target="_blank" class="act-btn act-print" title="Print Payslip">
                                <i class="bi bi-printer"></i>
                            </a>

                            {{-- Mark as Paid --}}
                            @if($p->status !== 'paid' && auth()->user()->hasPermission('payroll.edit'))
                            <button type="button" class="act-btn act-pay" onclick="openPaymentModal({{ $p->id }}, '{{ $p->payroll_number }}', '{{ $p->net_amount }}')" title="Mark as Paid">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            @endif

                            {{-- Delete --}}
                            @if(auth()->user()->hasPermission('payroll.delete'))
                            <button type="button" class="act-btn act-delete" onclick="deletePayroll({{ $p->id }}, '{{ $p->payroll_number }}')" title="Delete Payroll">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="delete-payroll-form-{{ $p->id }}" action="{{ route('admin.payrolls.destroy', $p) }}" method="POST" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="bi bi-cash-stack display-4 d-block mb-2 text-muted opacity-50"></i>
                        No payroll records found.
                        <div class="mt-2">
                            <a href="{{ route('admin.payrolls.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Process Weekly Payroll
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payrolls->hasPages())
    <div class="card-footer bg-white py-3">
        {{ $payrolls->links() }}
    </div>
    @endif
</div>

{{-- Quick Payment Modal --}}
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="paymentForm" method="POST" action="">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi bi-cash-coin me-2"></i>Mark Salary as Paid
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="status" value="paid">
                    <div class="mb-3">
                        <label class="form-label text-muted small">Payroll Reference</label>
                        <div class="fs-5 fw-bold text-dark" id="modalPayrollNum">--</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small">Total Net Pay Amount</label>
                        <div class="fs-4 fw-bold text-success" id="modalNetAmt">₹ 0.00</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer (NEFT / IMPS)</option>
                            <option value="UPI / GPay">UPI / GPay / PhonePe</option>
                            <option value="Cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reference / Transaction ID (Optional)</label>
                        <input type="text" name="payment_reference" class="form-control" placeholder="e.g. UPI Ref # or Voucher No">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check-circle me-1"></i>Confirm Paid
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let paymentModalInstance;
document.addEventListener('DOMContentLoaded', function() {
    paymentModalInstance = new bootstrap.Modal(document.getElementById('paymentModal'));
});

function openPaymentModal(id, number, net) {
    document.getElementById('paymentForm').action = "{{ url('admin/payrolls') }}/" + id + "/status";
    document.getElementById('modalPayrollNum').innerText = number;
    document.getElementById('modalNetAmt').innerText = '₹ ' + Number(net).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    paymentModalInstance.show();
}

function deletePayroll(id, number) {
    Swal.fire({
        title: 'Delete Payroll?',
        text: `Are you sure you want to delete payroll record ${number}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete!'
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById(`delete-payroll-form-${id}`).submit();
        }
    });
}
</script>
@endpush
