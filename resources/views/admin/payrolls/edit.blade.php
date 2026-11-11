@extends('layouts.app')
@section('title', 'Edit Payroll - ' . $payroll->payroll_number)

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title">Edit Payroll Slip</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.payrolls.index') }}">Payroll</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.payrolls.show', $payroll) }}">{{ $payroll->payroll_number }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.payrolls.show', $payroll) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back
    </a>
</div>

<form action="{{ route('admin.payrolls.update', $payroll) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-info-circle text-primary me-2"></i>Fixed Period & Base Calculations
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Employee</label>
                            <div class="fw-bold fs-6">{{ $payroll->employee?->name }} ({{ $payroll->employee?->employee_code }})</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Period</label>
                            <div class="fw-bold fs-6">{{ $payroll->start_date->format('d/m/Y') }} &mdash; {{ $payroll->end_date->format('d/m/Y') }} ({{ ucfirst($payroll->period_type) }})</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Days Worked</label>
                            <div class="fw-bold">{{ $payroll->total_working_days }} days</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Base Wage Earned</label>
                            <div class="fw-bold">{{ format_currency($payroll->base_salary_earned) }}</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label text-muted small">Extra Duty OT Earned</label>
                            <div class="fw-bold text-success">{{ format_currency($payroll->total_extra_duty_amount) }} ({{ $payroll->total_extra_duty_hours }} hrs)</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-sliders text-warning me-2"></i>Adjustments & Notes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-success">Allowance / Incentive (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" min="0" name="allowance_amount" class="form-control" value="{{ old('allowance_amount', $payroll->allowance_amount) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Allowance Description</label>
                            <input type="text" name="allowance_notes" class="form-control" value="{{ old('allowance_notes', $payroll->allowance_notes) }}" placeholder="e.g. Travel / food allowance">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-danger">Deduction / Advance Recovery (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" min="0" name="deduction_amount" class="form-control" value="{{ old('deduction_amount', $payroll->deduction_amount) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Deduction Reason</label>
                            <input type="text" name="deduction_notes" class="form-control" value="{{ old('deduction_notes', $payroll->deduction_notes) }}" placeholder="e.g. Advance deduction">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">General Notes</label>
                        <textarea name="notes" rows="2" class="form-control">{{ old('notes', $payroll->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold">
                        <i class="bi bi-credit-card text-success me-2"></i>Disbursement Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="draft" {{ old('status', $payroll->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="approved" {{ old('status', $payroll->status) === 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="paid" {{ old('status', $payroll->status) === 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Method</label>
                        <select name="payment_method" class="form-select">
                            <option value="Cash" {{ old('payment_method', $payroll->payment_method) === 'Cash' ? 'selected' : '' }}>Cash</option>
                            <option value="Bank Transfer" {{ old('payment_method', $payroll->payment_method) === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="UPI / GPay" {{ old('payment_method', $payroll->payment_method) === 'UPI / GPay' ? 'selected' : '' }}>UPI / GPay</option>
                            <option value="Cheque" {{ old('payment_method', $payroll->payment_method) === 'Cheque' ? 'selected' : '' }}>Cheque</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', $payroll->payment_date?->format('Y-m-d') ?: date('Y-m-d')) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Reference / Voucher</label>
                        <input type="text" name="payment_reference" class="form-control" value="{{ old('payment_reference', $payroll->payment_reference) }}">
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-circle me-1"></i>Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
