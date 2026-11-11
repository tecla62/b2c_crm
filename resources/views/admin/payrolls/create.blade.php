@extends('layouts.app')
@section('title', 'Process Salary & Wage Sheet')

@push('styles')
<style>
.wage-summary-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
}
.wage-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px dashed #e2e8f0;
    font-size: 14.5px;
}
.wage-row:last-child {
    border-bottom: none;
}
.wage-row.total-row {
    font-size: 18px;
    font-weight: 850;
    color: #0f172a;
    border-top: 2px solid #cbd5e1;
    border-bottom: none;
    margin-top: 10px;
    padding-top: 12px;
}
.quick-btn {
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 12px;
}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title">Process Salary & Wage Sheet</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.payrolls.index') }}">Payroll</a></li>
                <li class="breadcrumb-item active">New Wage Sheet</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.payrolls.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to Payrolls
    </a>
</div>

<form action="{{ route('admin.payrolls.store') }}" method="POST" id="payrollForm">
    @csrf

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0 fw-bold text-primary">
                <i class="bi bi-person-check-fill me-2"></i>1. Select Employee & Settlement Period
            </h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Employee / Staff <span class="text-danger">*</span></label>
                    <select name="employee_id" id="sel_employee_id" class="form-select @error('employee_id') is-invalid @enderror" required onchange="onEmployeeChanged()">
                        <option value="">-- Choose Employee --</option>
                        @foreach($employees as $e)
                        <option value="{{ $e->id }}"
                                {{ old('employee_id', $selectedEmployeeId) == $e->id ? 'selected' : '' }}
                                data-salary-type="{{ $e->salary_type }}"
                                data-settlement-days="{{ $e->settlement_days ?: 7 }}"
                                data-salary="{{ $e->base_salary }}"
                                data-extra-rate="{{ $e->extra_duty_rate }}">
                            {{ $e->name }} ({{ $e->employee_code }}) &bull; {{ $e->salary_type_label }}
                        </option>
                        @endforeach
                    </select>
                    @error('employee_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Settlement Period Scheme <span class="text-danger">*</span></label>
                    <select name="period_type" id="sel_period_type" class="form-select" required onchange="onPeriodTypeChanged()">
                        <option value="weekly" {{ old('period_type', 'weekly') === 'weekly' ? 'selected' : '' }}>Weekly (6 Days Cycle)</option>
                        <option value="monthly" {{ old('period_type') === 'monthly' ? 'selected' : '' }}>Monthly (30 Days)</option>
                        <option value="40_days" {{ old('period_type') === '40_days' ? 'selected' : '' }}>40 Days Settlement Cycle</option>
                        <option value="60_days" {{ old('period_type') === '60_days' ? 'selected' : '' }}>60 Days Settlement Cycle</option>
                        <option value="70_days" {{ old('period_type') === '70_days' ? 'selected' : '' }}>70 Days Settlement Cycle</option>
                        <option value="custom_days" {{ old('period_type') === 'custom_days' ? 'selected' : '' }}>Custom Date Range</option>
                        <option value="daily_ondemand" {{ old('period_type') === 'daily_ondemand' ? 'selected' : '' }}>Daily / Flexible</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-semibold">Period Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="start_date" id="sel_start_date" class="form-control"
                           value="{{ old('start_date', $defaultStart) }}" required onchange="fetchAttendanceData()">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Period End Date <span class="text-danger">*</span></label>
                    <input type="date" name="end_date" id="sel_end_date" class="form-control"
                           value="{{ old('end_date', $defaultEnd) }}" required onchange="fetchAttendanceData()">
                    <small class="text-muted" id="end_date_hint">Selected settlement date range.</small>
                </div>
            </div>

            {{-- Quick Date Range Shortcuts --}}
            <div class="mt-3 pt-3 border-top d-flex align-items-center gap-2 flex-wrap">
                <span class="small fw-bold text-muted text-uppercase me-1"><i class="bi bi-clock-history me-1"></i>Quick Cycles:</span>
                <button type="button" class="btn btn-outline-primary btn-sm quick-btn" onclick="setQuickWeek()">This Week (Mon–Sat)</button>
                <button type="button" class="btn btn-outline-secondary btn-sm quick-btn" onclick="setQuickMonth()">This Month (1st–Today)</button>
                <button type="button" class="btn btn-outline-info btn-sm quick-btn" onclick="setQuickDays(40)">Last 40 Days</button>
                <button type="button" class="btn btn-outline-purple btn-sm quick-btn" style="color:#7e22ce;border-color:#d8b4fe;" onclick="setQuickDays(60)">Last 60 Days</button>
                <button type="button" class="btn btn-outline-danger btn-sm quick-btn" onclick="setQuickDays(70)">Last 70 Days</button>

                {{-- Custom Days Selector (e.g. 69, 74, 75, 84...) --}}
                <div class="d-inline-flex align-items-center bg-white border rounded px-2 py-1 shadow-sm">
                    <span class="small fw-semibold text-secondary me-1">Custom:</span>
                    <input type="number" id="quickCustomDaysInput" class="form-control form-control-sm text-center fw-bold text-primary" style="width: 75px; height: 28px;" placeholder="75" min="1" max="365" value="75">
                    <button type="button" class="btn btn-primary btn-sm ms-1 py-0 px-2" style="height: 28px; line-height: 26px; font-size: 12px;" onclick="applyCustomQuickDays()" title="Apply custom days interval (e.g. 69, 74, 75, 84 days)">
                        Apply Days
                    </button>
                </div>

                <button type="button" class="btn btn-success btn-sm ms-auto" onclick="fetchAttendanceData()">
                    <i class="bi bi-arrow-repeat me-1"></i>Fetch & Calculate Attendance
                </button>
            </div>
        </div>
    </div>

    {{-- Attendance Breakdown & Live Wage Calculation --}}
    <div id="calculationSection" style="display: none;">
        {{-- Employee & Rates Header --}}
        <div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
            <div>
                <h6 class="alert-heading mb-1 fw-bold" id="empAlertName">Employee: Ramesh Kumar</h6>
                <div class="small">
                    Rate: <strong id="empAlertBaseRate">₹ 4,500.00</strong> &bull;
                    Equivalent Daily Rate: <strong id="empAlertDailyRate">₹ 750.00</strong> &bull;
                    Default Extra Duty OT Rate: <strong id="empAlertOtRate">₹ 100.00 / hr</strong>
                </div>
            </div>
            <div id="empSalaryBadge"></div>
        </div>

        {{-- Site Attendance Items Table --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold">
                    <i class="bi bi-geo-alt-fill text-danger me-2"></i>2. Itemized Site Attendance & Extra Duty Breakdown
                </h5>
                <span class="badge bg-primary" id="attendanceCountBadge">0 days</span>
            </div>
            <div class="table-responsive" style="max-height: 480px; overflow-y: auto;">
                <table class="table table-bordered table-hover align-middle mb-0" id="attendancePreviewTable">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th style="width: 140px;">Date & Day</th>
                            <th>Site / Job Location</th>
                            <th>In / Out Time</th>
                            <th>Status</th>
                            <th>Regular Hrs</th>
                            <th>Extra Duty (OT) Details</th>
                            <th class="text-end" style="width: 130px;">OT Pay (₹)</th>
                        </tr>
                    </thead>
                    <tbody id="attendancePreviewBody">
                        {{-- Injected dynamically --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Summary, Allowances, Deductions & Net Payout --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="bi bi-sliders me-2 text-warning"></i>3. Adjustments & Allowances
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-success">
                                    <i class="bi bi-plus-circle me-1"></i>Allowance / Incentive (₹)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" min="0" name="allowance_amount" id="allowance_amount"
                                           class="form-control" value="0.00" oninput="recalcTotals()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Allowance Description</label>
                                <input type="text" name="allowance_notes" id="allowance_notes" class="form-control"
                                       placeholder="e.g. Travel / Food allowance / Bonus">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-danger">
                                    <i class="bi bi-dash-circle me-1"></i>Deduction / Advance Recovery (₹)
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" min="0" name="deduction_amount" id="deduction_amount"
                                           class="form-control" value="0.00" oninput="recalcTotals()">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Deduction Reason</label>
                                <input type="text" name="deduction_notes" id="deduction_notes" class="form-control"
                                       placeholder="e.g. Salary advance taken / Tool loss">
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payroll Status</label>
                                <select name="status" id="payroll_status" class="form-select" onchange="togglePaymentInputs(this.value)">
                                    <option value="draft">Draft</option>
                                    <option value="approved">Approved</option>
                                    <option value="paid" selected>Paid / Disbursed</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Payment Method</label>
                                <select name="payment_method" id="payment_method" class="form-select">
                                    <option value="Cash">Cash</option>
                                    <option value="Bank Transfer">Bank Transfer (NEFT / IMPS)</option>
                                    <option value="UPI / GPay">UPI / GPay / PhonePe</option>
                                    <option value="Cheque">Cheque</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="payment_date_col">
                                <label class="form-label fw-semibold">Disbursement Date</label>
                                <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-6" id="payment_ref_col">
                                <label class="form-label fw-semibold">Reference / Voucher No</label>
                                <input type="text" name="payment_reference" id="payment_reference" class="form-control" placeholder="Optional ref #">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">General Notes</label>
                                <textarea name="notes" rows="2" class="form-control" placeholder="Notes for worker / record"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-success">
                            <i class="bi bi-receipt me-2"></i>4. Salary Calculation Summary
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="wage-summary-box">
                            <div class="wage-row">
                                <span class="text-muted">Total Working Days Worked:</span>
                                <strong><span id="sum_working_days">0</span> days</strong>
                            </div>
                            <div class="wage-row">
                                <span class="text-muted">Days Breakdown:</span>
                                <span><span id="sum_present">0</span> Present, <span id="sum_half_day">0</span> Half Day</span>
                            </div>
                            <div class="wage-row">
                                <span class="text-muted">Base Wages Earned:</span>
                                <strong class="text-dark" id="disp_base_earned">₹ 0.00</strong>
                            </div>
                            <div class="wage-row">
                                <span class="text-muted">Total Extra Duty OT (<span id="sum_ot_hours">0</span> hrs):</span>
                                <strong class="text-success" id="disp_ot_earned">+ ₹ 0.00</strong>
                            </div>
                            <div class="wage-row">
                                <span class="text-muted">Allowances / Bonuses:</span>
                                <strong class="text-success" id="disp_allowance">+ ₹ 0.00</strong>
                            </div>
                            <div class="wage-row">
                                <span class="text-muted">Deductions / Advances:</span>
                                <strong class="text-danger" id="disp_deduction">- ₹ 0.00</strong>
                            </div>
                            <div class="wage-row total-row">
                                <span>Net Payable Salary:</span>
                                <span class="text-primary" id="disp_net_amount">₹ 0.00</span>
                            </div>
                        </div>

                        <div class="mt-4 d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-file-earmark-check me-2"></i>Generate & Save Wage Sheet
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Indicator --}}
    <div id="loadingIndicator" class="text-center py-5 text-muted" style="display: none;">
        <div class="spinner-border text-primary mb-2" role="status"></div>
        <p>Fetching site attendance and computing wages for selected period...</p>
    </div>
</form>
@endsection

@push('scripts')
<script>
let currentPeriodData = null;
const EMPLOYEES_BASE_URL = "{{ url('admin/employees') }}";
const ATTENDANCE_PERIOD_URL = "{{ route('admin.attendance.period-data') }}";

function onEmployeeChanged() {
    const sel = document.getElementById('sel_employee_id');
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;

    const empId = opt.value;
    fetch(`${EMPLOYEES_BASE_URL}/${empId}/rate`)
        .then(res => res.json())
        .then(emp => {
            document.getElementById('sel_period_type').value = emp.salary_type || 'weekly';
            onPeriodTypeChanged();

            const today = new Date().toISOString().split('T')[0];
            let hasValidRange = false;

            if (emp.last_payroll_end) {
                // Resume from next day after last payroll if it's on or before today
                const nextDay = new Date(emp.last_payroll_end);
                nextDay.setDate(nextDay.getDate() + 1);
                const nextDayStr = nextDay.toISOString().split('T')[0];

                if (nextDayStr <= today) {
                    document.getElementById('sel_start_date').value = nextDayStr;
                    document.getElementById('sel_end_date').value = today;
                    hasValidRange = true;
                }
            }

            if (!hasValidRange) {
                // If no previous payroll OR previous payroll already covers up to today,
                // set the default period interval ending today so start_date is never in future
                if (emp.salary_type === '40_days') {
                    setQuickDays(40);
                    return;
                } else if (emp.salary_type === '60_days') {
                    setQuickDays(60);
                    return;
                } else if (emp.salary_type === '70_days') {
                    setQuickDays(70);
                    return;
                } else if (emp.salary_type === 'custom_days' || (emp.settlement_days && ![1, 7, 30, 40, 60, 70].includes(parseInt(emp.settlement_days)))) {
                    const cDays = parseInt(emp.settlement_days) || 75;
                    const customInp = document.getElementById('quickCustomDaysInput');
                    if (customInp) customInp.value = cDays;
                    setQuickDays(cDays);
                    return;
                } else if (emp.salary_type === 'monthly') {
                    setQuickMonth();
                    return;
                } else {
                    setQuickWeek();
                    return;
                }
            }
            fetchAttendanceData();
        })
        .catch(err => {
            console.error(err);
            fetchAttendanceData();
        });
}

function onPeriodTypeChanged() {
    const type = document.getElementById('sel_period_type').value;
    const hint = document.getElementById('end_date_hint');
    if (type === 'weekly') {
        hint.innerText = '6-day work week cycle (Mon to Sat).';
    } else if (type === 'monthly') {
        hint.innerText = 'Monthly settlement cycle (30 days).';
    } else if (type === '40_days') {
        hint.innerText = '40 days settlement interval.';
    } else if (type === '60_days') {
        hint.innerText = '60 days settlement interval.';
    } else if (type === '70_days') {
        hint.innerText = '70 days settlement interval.';
    } else {
        hint.innerText = 'Custom period date range (e.g. 69, 74, 75, 84 days).';
    }
}

function applyCustomQuickDays() {
    const days = parseInt(document.getElementById('quickCustomDaysInput').value);
    if (!days || days < 1) {
        alert('Please enter a valid number of days (e.g. 69, 74, 75, 84).');
        return;
    }
    setQuickDays(days);
}

function setQuickWeek() {
    const now = new Date();
    const day = now.getDay();
    const diff = now.getDate() - day + (day === 0 ? -6 : 1);
    const monday = new Date(now.setDate(diff));
    const saturday = new Date(monday);
    saturday.setDate(monday.getDate() + 5);

    document.getElementById('sel_period_type').value = 'weekly';
    document.getElementById('sel_start_date').value = monday.toISOString().split('T')[0];
    document.getElementById('sel_end_date').value = saturday.toISOString().split('T')[0];
    onPeriodTypeChanged();
    fetchAttendanceData();
}

function setQuickMonth() {
    const now = new Date();
    const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
    document.getElementById('sel_period_type').value = 'monthly';
    document.getElementById('sel_start_date').value = firstDay.toISOString().split('T')[0];
    document.getElementById('sel_end_date').value = new Date().toISOString().split('T')[0];
    onPeriodTypeChanged();
    fetchAttendanceData();
}

function setQuickDays(numDays) {
    const today = new Date();
    const past = new Date();
    past.setDate(today.getDate() - (numDays - 1));

    if (numDays === 40) document.getElementById('sel_period_type').value = '40_days';
    else if (numDays === 60) document.getElementById('sel_period_type').value = '60_days';
    else if (numDays === 70) document.getElementById('sel_period_type').value = '70_days';
    else {
        document.getElementById('sel_period_type').value = 'custom_days';
        const customInp = document.getElementById('quickCustomDaysInput');
        if (customInp) customInp.value = numDays;
    }

    document.getElementById('sel_start_date').value = past.toISOString().split('T')[0];
    document.getElementById('sel_end_date').value = today.toISOString().split('T')[0];
    onPeriodTypeChanged();
    fetchAttendanceData();
}

function fetchAttendanceData() {
    const empId = document.getElementById('sel_employee_id').value;
    let start = document.getElementById('sel_start_date').value;
    let end   = document.getElementById('sel_end_date').value;

    if (!empId || !start || !end) return;

    // Safety: ensure start is never greater than end
    if (start > end) {
        document.getElementById('sel_end_date').value = start;
        end = start;
    }

    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('calculationSection').style.display = 'none';

    fetch(`${ATTENDANCE_PERIOD_URL}?employee_id=${empId}&start_date=${start}&end_date=${end}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(async res => {
            let data;
            try {
                data = await res.json();
            } catch(e) {
                throw new Error(`Server returned HTTP ${res.status} (${res.statusText})`);
            }
            if (!res.ok) {
                const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Server returned an error');
                throw new Error(errMsg);
            }
            return data;
        })
        .then(data => {
            document.getElementById('loadingIndicator').style.display = 'none';
            document.getElementById('calculationSection').style.display = 'block';
            currentPeriodData = data;
            renderAttendancePreview(data);
        })
        .catch(err => {
            document.getElementById('loadingIndicator').style.display = 'none';
            console.error('Fetch error:', err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Attendance Fetch Failed',
                    text: err.message || 'Failed to load attendance records for this employee.'
                });
            } else {
                alert(err.message || 'Failed to load attendance records for this employee.');
            }
        });
}

function renderAttendancePreview(data) {
    const emp = data.employee;
    const sum = data.summary;
    const items = data.attendances;

    document.getElementById('empAlertName').innerText = `Employee: ${emp.name} (${emp.employee_code})`;
    document.getElementById('empAlertBaseRate').innerText = '₹ ' + Number(emp.base_salary).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    
    let divisor = emp.settlement_days || 7;
    if (emp.salary_type === 'weekly') divisor = 6;
    else if (emp.salary_type === 'monthly') divisor = 30;
    else if (emp.salary_type === '40_days') divisor = 40;
    else if (emp.salary_type === '60_days') divisor = 60;
    else if (emp.salary_type === '70_days') divisor = 70;
    else if (emp.salary_type === 'daily_ondemand') divisor = 1;

    const dailyRate = (emp.base_salary / divisor);
    document.getElementById('empAlertDailyRate').innerText = '₹ ' + Number(dailyRate).toLocaleString('en-IN', { minimumFractionDigits: 2 }) + ' / day';
    document.getElementById('empAlertOtRate').innerText = '₹ ' + Number(emp.extra_duty_rate).toLocaleString('en-IN', { minimumFractionDigits: 2 }) + ' / hr';

    document.getElementById('attendanceCountBadge').innerText = `${items.length} days recorded`;

    const tbody = document.getElementById('attendancePreviewBody');
    tbody.innerHTML = '';

    if (items.length === 0) {
        tbody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-warning">
            <i class="bi bi-exclamation-triangle me-1"></i>No site attendance punches found for this period (${document.getElementById('sel_start_date').value} to ${document.getElementById('sel_end_date').value}).
            Please ensure attendance has been recorded in <a href="{{ route('admin.attendance.index') }}" target="_blank">Daily Attendance</a>.
        </td></tr>`;
    } else {
        items.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="fw-bold">${item.date_formatted}</div>
                </td>
                <td>
                    <span class="fw-semibold text-primary"><i class="bi bi-geo-alt me-1"></i>${item.site_name}</span>
                    ${item.notes ? `<div class="text-muted small">${item.notes}</div>` : ''}
                </td>
                <td>
                    <span class="small">${item.punch_in} - ${item.punch_out}</span>
                </td>
                <td>${item.status_badge}</td>
                <td><strong>${item.regular_hours}</strong> hrs</td>
                <td>
                    ${item.has_extra_duty && item.extra_duty_hours > 0 ? `
                        <div class="text-success fw-bold">
                            <i class="bi bi-clock me-1"></i>${item.extra_duty_hours} hrs &times; ₹${item.extra_duty_rate}
                        </div>
                        <div class="small text-muted">${item.extra_duty_job}</div>
                    ` : '<span class="text-muted small">None</span>'}
                </td>
                <td class="text-end">
                    <strong class="${item.extra_duty_amount > 0 ? 'text-success' : 'text-muted'}">
                        ₹ ${Number(item.extra_duty_amount).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                    </strong>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    document.getElementById('sum_working_days').innerText = sum.total_working_days;
    document.getElementById('sum_present').innerText = sum.days_present;
    document.getElementById('sum_half_day').innerText = sum.days_half_day;
    document.getElementById('sum_ot_hours').innerText = sum.total_extra_duty_hours;

    recalcTotals();
}

function recalcTotals() {
    if (!currentPeriodData) return;
    const sum = currentPeriodData.summary;

    const baseEarned = sum.base_salary_earned;
    const otEarned = sum.total_extra_duty_amount;
    const allowance = parseFloat(document.getElementById('allowance_amount').value) || 0;
    const deduction = parseFloat(document.getElementById('deduction_amount').value) || 0;

    const gross = baseEarned + otEarned + allowance;
    const net = Math.max(0, gross - deduction);

    document.getElementById('disp_base_earned').innerText = '₹ ' + Number(baseEarned).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    document.getElementById('disp_ot_earned').innerText = '+ ₹ ' + Number(otEarned).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    document.getElementById('disp_allowance').innerText = '+ ₹ ' + Number(allowance).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    document.getElementById('disp_deduction').innerText = '- ₹ ' + Number(deduction).toLocaleString('en-IN', { minimumFractionDigits: 2 });
    document.getElementById('disp_net_amount').innerText = '₹ ' + Number(net).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}

function togglePaymentInputs(status) {
    const isPaid = status === 'paid';
    document.getElementById('payment_date_col').style.display = isPaid ? 'block' : 'none';
    document.getElementById('payment_ref_col').style.display = isPaid ? 'block' : 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('sel_employee_id').value) {
        onEmployeeChanged();
    }
});
</script>
@endpush
