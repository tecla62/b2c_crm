@extends('layouts.app')
@section('title', $employee->exists ? 'Edit Employee - ' . $employee->name : 'Add New Employee')

@push('styles')
<style>
.salary-option-card {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 16px;
    cursor: pointer;
    transition: all .15s ease;
    height: 100%;
    display: block;
}
.salary-option-card:hover {
    border-color: #6366f1;
    background: #f8fafc;
}
.salary-option-card.active {
    border-color: #4f46e5;
    background: #eef2ff;
}
.salary-option-card input[type="radio"] {
    margin-right: 8px;
}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title">{{ $employee->exists ? 'Edit Employee' : 'Add New Employee' }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Employees</a></li>
                <li class="breadcrumb-item active">{{ $employee->exists ? $employee->name : 'New' }}</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to List
    </a>
</div>

<form action="{{ $employee->exists ? route('admin.employees.update', $employee) : route('admin.employees.store') }}" method="POST">
    @csrf
    @if($employee->exists)
        @method('PUT')
    @endif

    <div class="row g-4">
        {{-- Basic Information --}}
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-person-lines-fill text-primary me-2"></i>Personal & Role Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Employee Code <span class="text-danger">*</span></label>
                            <input type="text" name="employee_code" class="form-control @error('employee_code') is-invalid @enderror"
                                   value="{{ old('employee_code', $employee->employee_code) }}" required>
                            @error('employee_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   placeholder="e.g. Ramesh Kumar"
                                   value="{{ old('name', $employee->name) }}" required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="mobile" class="form-control @error('mobile') is-invalid @enderror"
                                       placeholder="e.g. 9876543210"
                                       value="{{ old('mobile', $employee->mobile) }}">
                            </div>
                            @error('mobile')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Designation / Role</label>
                            <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror"
                                   placeholder="e.g. Waterproofing Master / Technician / Mason"
                                   value="{{ old('designation', $employee->designation) }}">
                            @error('designation')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Joining Date</label>
                            <input type="date" name="joining_date" class="form-control @error('joining_date') is-invalid @enderror"
                                   value="{{ old('joining_date', $employee->joining_date?->format('Y-m-d') ?: date('Y-m-d')) }}">
                            @error('joining_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                <option value="active" {{ old('status', $employee->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $employee->status) === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Address</label>
                            <textarea name="address" rows="2" class="form-control" placeholder="Residential / native address">{{ old('address', $employee->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Salary & Wages Configuration --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold text-success"><i class="bi bi-cash-coin me-2"></i>Salary & Wage Configuration</h5>
                    <small class="text-muted">Choose when and how this worker receives wages (Weekly, Monthly, 40 Days, 60 Days, 70 Days, or On-Demand).</small>
                </div>
                <div class="card-body">
                    {{-- Salary Scheme Selection --}}
                    <label class="form-label fw-semibold mb-2">Wage / Salary Scheme <span class="text-danger">*</span></label>
                    
                    @php 
                        $currentType = old('salary_type', $employee->salary_type ?? 'weekly'); 
                        $settlementDays = old('settlement_days', $employee->settlement_days ?? 7);
                    @endphp

                    <div class="row g-3 mb-4">
                        {{-- Weekly (6 Days) --}}
                        <div class="col-md-4">
                            <label class="salary-option-card {{ $currentType === 'weekly' ? 'active' : '' }}" for="sal_weekly">
                                <div class="d-flex align-items-center mb-1">
                                    <input type="radio" name="salary_type" id="sal_weekly" value="weekly" {{ $currentType === 'weekly' ? 'checked' : '' }} onchange="updateSalaryLabels('weekly', 7)">
                                    <strong class="text-primary">Weekly Wages</strong>
                                </div>
                                <div class="small text-muted ps-4">Paid every week (6 working days cycle, Mon–Sat payout).</div>
                            </label>
                        </div>

                        {{-- Monthly (30 Days) --}}
                        <div class="col-md-4">
                            <label class="salary-option-card {{ $currentType === 'monthly' ? 'active' : '' }}" for="sal_monthly">
                                <div class="d-flex align-items-center mb-1">
                                    <input type="radio" name="salary_type" id="sal_monthly" value="monthly" {{ $currentType === 'monthly' ? 'checked' : '' }} onchange="updateSalaryLabels('monthly', 30)">
                                    <strong class="text-warning text-dark">Monthly Salary</strong>
                                </div>
                                <div class="small text-muted ps-4">Fixed monthly salary with attendance tracking (30 days).</div>
                            </label>
                        </div>

                        {{-- 40 Days Settlement --}}
                        <div class="col-md-4">
                            <label class="salary-option-card {{ $currentType === '40_days' ? 'active' : '' }}" for="sal_40_days">
                                <div class="d-flex align-items-center mb-1">
                                    <input type="radio" name="salary_type" id="sal_40_days" value="40_days" {{ $currentType === '40_days' ? 'checked' : '' }} onchange="updateSalaryLabels('40_days', 40)">
                                    <strong class="text-info text-dark">40 Days Cycle</strong>
                                </div>
                                <div class="small text-muted ps-4">Settled once in 40 days (40 days cumulative site work).</div>
                            </label>
                        </div>

                        {{-- 60 Days Settlement --}}
                        <div class="col-md-4">
                            <label class="salary-option-card {{ $currentType === '60_days' ? 'active' : '' }}" for="sal_60_days">
                                <div class="d-flex align-items-center mb-1">
                                    <input type="radio" name="salary_type" id="sal_60_days" value="60_days" {{ $currentType === '60_days' ? 'checked' : '' }} onchange="updateSalaryLabels('60_days', 60)">
                                    <strong style="color:#7e22ce;">60 Days Cycle</strong>
                                </div>
                                <div class="small text-muted ps-4">Settled once in 60 days (bi-monthly milestone payout).</div>
                            </label>
                        </div>

                        {{-- 70 Days Settlement --}}
                        <div class="col-md-4">
                            <label class="salary-option-card {{ $currentType === '70_days' ? 'active' : '' }}" for="sal_70_days">
                                <div class="d-flex align-items-center mb-1">
                                    <input type="radio" name="salary_type" id="sal_70_days" value="70_days" {{ $currentType === '70_days' ? 'checked' : '' }} onchange="updateSalaryLabels('70_days', 70)">
                                    <strong style="color:#86198f;">70 Days Cycle</strong>
                                </div>
                                <div class="small text-muted ps-4">Settled once in 70 days (project interval payout).</div>
                            </label>
                        </div>

                        {{-- Daily / Flexible --}}
                        <div class="col-md-4">
                            <label class="salary-option-card {{ $currentType === 'daily_ondemand' ? 'active' : '' }}" for="sal_daily">
                                <div class="d-flex align-items-center mb-1">
                                    <input type="radio" name="salary_type" id="sal_daily" value="daily_ondemand" {{ $currentType === 'daily_ondemand' ? 'checked' : '' }} onchange="updateSalaryLabels('daily_ondemand', 1)">
                                    <strong class="text-success">Daily / Flexible</strong>
                                </div>
                                <div class="small text-muted ps-4">Paid on demand for each day worked (flexible daily wage).</div>
                            </label>
                        </div>

                        {{-- Custom Days Settlement (Any Interval: 69, 74, 75, 84...) --}}
                        <div class="col-md-4">
                            <label class="salary-option-card {{ $currentType === 'custom_days' ? 'active' : '' }}" for="sal_custom_days" style="border-color: #0284c7;">
                                <div class="d-flex align-items-center mb-1">
                                    <input type="radio" name="salary_type" id="sal_custom_days" value="custom_days" {{ $currentType === 'custom_days' ? 'checked' : '' }} onchange="updateSalaryLabels('custom_days', document.getElementById('settlement_days').value || 75)">
                                    <strong style="color:#0369a1;"><i class="bi bi-sliders me-1"></i>Custom Days Interval</strong>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-auto" style="font-size: 10px;">Customizable</span>
                                </div>
                                <div class="small text-muted ps-4">Set ANY exact number of days (e.g. 69, 74, 75, 84 days). 100% customizable.</div>
                            </label>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6" id="settlementDaysCol" style="display: {{ $currentType === 'custom_days' ? 'block' : 'none' }};">
                            <label class="form-label fw-semibold text-primary">
                                <i class="bi bi-calendar3-range me-1"></i>Settlement Interval (Enter Number of Days) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" min="1" max="365" name="settlement_days" id="settlement_days"
                                       class="form-control fw-bold fs-6 text-primary"
                                       value="{{ $settlementDays ?: 75 }}"
                                       oninput="onCustomDaysInput()"
                                       placeholder="e.g. 69, 74, 75, 84">
                                <span class="input-group-text bg-light fw-bold text-dark">Days</span>
                            </div>
                            <small class="text-muted">Enter any custom number of days (e.g. 69, 74, 75, 84). The base salary will be divided by this exact number of days to get the daily wage.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" id="baseSalaryLabel">
                                Base Salary Rate (₹) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="base_salary" id="base_salary"
                                       class="form-control @error('base_salary') is-invalid @enderror"
                                       placeholder="e.g. 4500.00"
                                       value="{{ old('base_salary', $employee->base_salary) }}" required oninput="calcDailyEquivalent()">
                            </div>
                            <small class="text-muted" id="baseSalaryHint">Weekly base salary for 6 work days.</small>
                            <div class="mt-1 fw-semibold text-primary small" id="dailyEqHint"></div>
                            @error('base_salary')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Default Extra Duty / OT Rate (₹ per Hour)
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="extra_duty_rate" id="extra_duty_rate"
                                       class="form-control @error('extra_duty_rate') is-invalid @enderror"
                                       placeholder="e.g. 100.00"
                                       value="{{ old('extra_duty_rate', $employee->extra_duty_rate ?? 0) }}">
                                <span class="input-group-text">/ hr</span>
                            </div>
                            <small class="text-muted">Default OT rate. Can be modified per job/site during punching.</small>
                            @error('extra_duty_rate')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Standard Daily Work Hours</label>
                            <div class="input-group">
                                <input type="number" step="0.5" name="daily_working_hours"
                                       class="form-control @error('daily_working_hours') is-invalid @enderror"
                                       placeholder="8"
                                       value="{{ old('daily_working_hours', $employee->daily_working_hours ?? 8) }}">
                                <span class="input-group-text">hours/day</span>
                            </div>
                            <small class="text-muted">Standard daily shift duration (e.g. 8 hours/day).</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Side column: Bank & Notes --}}
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-bank me-2 text-primary"></i>Bank / UPI Details</h5>
                    <small class="text-muted">Used for direct wage disbursement / payouts</small>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Bank Name</label>
                        <input type="text" name="bank_name" class="form-control" placeholder="e.g. State Bank of India"
                               value="{{ old('bank_name', $employee->bank_name) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Account Number</label>
                        <input type="text" name="account_number" class="form-control" placeholder="Account No"
                               value="{{ old('account_number', $employee->account_number) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">IFSC Code</label>
                        <input type="text" name="ifsc_code" class="form-control text-uppercase" placeholder="SBIN0001234"
                               value="{{ old('ifsc_code', $employee->ifsc_code) }}">
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-journal-text me-2"></i>Notes / Remarks</h5>
                </div>
                <div class="card-body">
                    <textarea name="notes" rows="3" class="form-control" placeholder="Any internal notes or special agreements">{{ old('notes', $employee->notes) }}</textarea>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-check-circle me-1"></i>{{ $employee->exists ? 'Save Changes' : 'Create Employee' }}
                </button>
                <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
let activeDays = 7;

function onCustomDaysInput() {
    const days = parseInt(document.getElementById('settlement_days').value) || 1;
    activeDays = days;
    const baseLabel = document.getElementById('baseSalaryLabel');
    const baseHint  = document.getElementById('baseSalaryHint');
    if (baseLabel) baseLabel.innerHTML = days + '-Day Base Salary (₹) <span class="text-danger">*</span>';
    if (baseHint) baseHint.innerText = 'Salary rate for ' + days + ' days cycle. Daily rate = Rate ÷ ' + days + '.';
    calcDailyEquivalent();
}

function updateSalaryLabels(type, days) {
    activeDays = days;
    document.querySelectorAll('.salary-option-card').forEach(c => c.classList.remove('active'));
    const selectedInput = document.querySelector(`input[name="salary_type"][value="${type}"]`);
    if (selectedInput) {
        selectedInput.closest('.salary-option-card').classList.add('active');
    }

    const daysInput = document.getElementById('settlement_days');
    const baseLabel = document.getElementById('baseSalaryLabel');
    const baseHint  = document.getElementById('baseSalaryHint');
    const customCol = document.getElementById('settlementDaysCol');

    if (type === 'weekly') {
        if (daysInput) daysInput.value = 7;
        baseLabel.innerHTML = 'Weekly Base Salary (₹) <span class="text-danger">*</span>';
        baseHint.innerText = 'Rate for 6 working days (Mon–Sat). Daily rate = Rate ÷ 6.';
        customCol.style.display = 'none';
    } else if (type === 'monthly') {
        if (daysInput) daysInput.value = 30;
        baseLabel.innerHTML = 'Monthly Salary (₹) <span class="text-danger">*</span>';
        baseHint.innerText = 'Fixed salary rate for 30 days. Daily rate = Rate ÷ 30.';
        customCol.style.display = 'none';
    } else if (type === '40_days') {
        if (daysInput) daysInput.value = 40;
        baseLabel.innerHTML = '40-Day Base Salary (₹) <span class="text-danger">*</span>';
        baseHint.innerText = 'Salary rate for 40 days cycle. Daily rate = Rate ÷ 40.';
        customCol.style.display = 'none';
    } else if (type === '60_days') {
        if (daysInput) daysInput.value = 60;
        baseLabel.innerHTML = '60-Day Base Salary (₹) <span class="text-danger">*</span>';
        baseHint.innerText = 'Salary rate for 60 days cycle. Daily rate = Rate ÷ 60.';
        customCol.style.display = 'none';
    } else if (type === '70_days') {
        if (daysInput) daysInput.value = 70;
        baseLabel.innerHTML = '70-Day Base Salary (₹) <span class="text-danger">*</span>';
        baseHint.innerText = 'Salary rate for 70 days cycle. Daily rate = Rate ÷ 70.';
        customCol.style.display = 'none';
    } else if (type === 'custom_days') {
        const curDays = parseInt(daysInput ? daysInput.value : 0) || (days || 75);
        activeDays = curDays;
        if (daysInput) daysInput.value = curDays;
        baseLabel.innerHTML = curDays + '-Day Base Salary (₹) <span class="text-danger">*</span>';
        baseHint.innerText = 'Salary rate for ' + curDays + ' days cycle. Daily rate = Rate ÷ ' + curDays + '.';
        customCol.style.display = 'block';
    } else {
        if (daysInput) daysInput.value = 1;
        baseLabel.innerHTML = 'Daily Wage Rate (₹) <span class="text-danger">*</span>';
        baseHint.innerText = 'Daily wage paid on demand for every working day.';
        customCol.style.display = 'none';
    }

    calcDailyEquivalent();
}

function calcDailyEquivalent() {
    const amount = parseFloat(document.getElementById('base_salary').value) || 0;
    const type = document.querySelector('input[name="salary_type"]:checked')?.value || 'weekly';
    const hint = document.getElementById('dailyEqHint');

    if (amount <= 0) {
        hint.innerHTML = '';
        return;
    }

    let divisor = activeDays;
    if (type === 'weekly') divisor = 6;
    else if (type === 'monthly') divisor = 30;
    else if (type === '40_days') divisor = 40;
    else if (type === '60_days') divisor = 60;
    else if (type === '70_days') divisor = 70;
    else if (type === 'daily_ondemand') divisor = 1;
    else {
        divisor = parseInt(document.getElementById('settlement_days').value) || (activeDays || 75);
    }

    if (type === 'daily_ondemand') {
        hint.innerHTML = `<i class="bi bi-info-circle me-1"></i>Worker gets ₹ ${amount.toLocaleString('en-IN', {minimumFractionDigits: 2})} per working day.`;
    } else {
        const daily = (amount / divisor).toFixed(2);
        hint.innerHTML = `<i class="bi bi-calculator me-1"></i>Equivalent Daily Wage: <strong>₹ ${Number(daily).toLocaleString('en-IN', {minimumFractionDigits: 2})}</strong> per day (over ${divisor} days)`;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const checked = document.querySelector('input[name="salary_type"]:checked');
    if (checked) {
        let days = 7;
        if (checked.value === 'weekly') days = 7;
        else if (checked.value === 'monthly') days = 30;
        else if (checked.value === '40_days') days = 40;
        else if (checked.value === '60_days') days = 60;
        else if (checked.value === '70_days') days = 70;
        else if (checked.value === 'custom_days') days = parseInt(document.getElementById('settlement_days')?.value) || 75;
        else if (checked.value === 'daily_ondemand') days = 1;
        updateSalaryLabels(checked.value, days);
    }
});
</script>
@endpush
