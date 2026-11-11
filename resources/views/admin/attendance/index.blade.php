@extends('layouts.app')
@section('title', 'Daily Attendance & Site Punching')

@push('styles')
<style>
.att-stat {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    padding: 16px;
    height: 100%;
}
.att-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .5px;
    color: #94a3b8;
    margin-bottom: 4px;
}
.att-value {
    font-size: 22px;
    font-weight: 850;
    color: #0f172a;
}
.ot-card {
    background: #f0fdf4;
    border: 1px dashed #86efac;
    border-radius: 12px;
    padding: 16px;
}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title">Daily Attendance & Site Punching</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.employees.index') }}">Staff</a></li>
                <li class="breadcrumb-item active">Attendance</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.payrolls.create') }}" class="btn btn-outline-success">
            <i class="bi bi-calculator me-1"></i>Process Weekly Payroll
        </a>
        @if(auth()->user()->hasPermission('attendance.create'))
        <button type="button" class="btn btn-primary" onclick="openPunchModal()">
            <i class="bi bi-clock-history me-1"></i>Record Attendance / Punch In
        </button>
        @endif
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="att-stat">
            <div class="att-label">Total Punches</div>
            <div class="att-value">{{ $summary['total_punches'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="att-stat">
            <div class="att-label text-success">Present</div>
            <div class="att-value text-success">{{ $summary['present'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="att-stat">
            <div class="att-label text-warning">Half Day</div>
            <div class="att-value text-warning">{{ $summary['half_day'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="att-stat">
            <div class="att-label text-danger">Absent</div>
            <div class="att-value text-danger">{{ $summary['absent'] }}</div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="att-stat" style="border-left: 4px solid #10b981;">
            <div class="att-label text-success">Total Extra Duty OT</div>
            <div class="att-value text-success">
                {{ $summary['total_extra_hours'] }} hrs
                <div style="font-size:13px;font-weight:600;color:#059669;">{{ format_currency($summary['total_extra_amount']) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Filter & Date Toolbar --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('admin.attendance.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-calendar-event"></i></span>
                    <input type="date" name="date" class="form-control" value="{{ request('date', $date) }}">
                </div>
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
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                    <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                    <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                    <option value="holiday" {{ request('status') == 'holiday' ? 'selected' : '' }}>Holiday</option>
                    <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>Leave</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <input type="text" name="site_name" class="form-control" placeholder="Search site name..." value="{{ request('site_name') }}">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary flex-fill">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                @if(request()->hasAny(['date', 'employee_id', 'status', 'site_name']))
                <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i>
                </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Attendance Records Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0 fw-bold">
            <i class="bi bi-list-check text-primary me-2"></i>Attendance Log for {{ \Carbon\Carbon::parse($date)->format('d M Y (l)') }}
        </h5>
        <span class="badge bg-light text-dark border">{{ $attendances->total() }} records</span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="min-width: 100px;">Date</th>
                    <th style="min-width: 170px;">Employee</th>
                    <th style="min-width: 180px;">Site / Job Location</th>
                    <th style="min-width: 140px;">Shift Times</th>
                    <th style="min-width: 110px;">Status</th>
                    <th style="min-width: 110px;">Regular Hours</th>
                    <th style="min-width: 160px;">Extra Duty (OT)</th>
                    <th style="min-width: 120px; width: 120px; text-align: right; padding-right: 1.5rem !important;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $att)
                <tr>
                    <td>
                        <div class="fw-bold">{{ $att->date->format('d/m/Y') }}</div>
                        <small class="text-muted">{{ $att->date->format('l') }}</small>
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $att->employee?->name ?? 'Unknown' }}</div>
                        <small class="badge bg-light text-dark border">{{ $att->employee?->employee_code }}</small>
                        <small class="text-muted ms-1">{{ $att->employee?->salary_type_label }}</small>
                    </td>
                    <td>
                        <div class="fw-semibold text-primary">
                            <i class="bi bi-geo-alt me-1"></i>{{ $att->site_name ?: ($att->customer?->name ? $att->customer->name . ' Site' : 'Main Site') }}
                        </div>
                        @if($att->customer)
                        <small class="text-muted">Client: {{ $att->customer->name }}</small>
                        @endif
                    </td>
                    <td>
                        <div class="small">
                            <span class="text-success"><i class="bi bi-box-arrow-in-right me-1"></i>In: {{ $att->punch_in ? \Carbon\Carbon::parse($att->punch_in)->format('h:i A') : '--' }}</span>
                            <br>
                            <span class="text-danger"><i class="bi bi-box-arrow-right me-1"></i>Out: {{ $att->punch_out ? \Carbon\Carbon::parse($att->punch_out)->format('h:i A') : '--' }}</span>
                        </div>
                    </td>
                    <td>
                        {!! $att->status_badge !!}
                    </td>
                    <td>
                        <span class="fw-semibold">{{ $att->regular_hours }} hrs</span>
                    </td>
                    <td>
                        @if($att->has_extra_duty && $att->extra_duty_hours > 0)
                        <div class="text-success fw-bold">
                            <i class="bi bi-clock me-1"></i>{{ $att->extra_duty_hours }} hrs &times; {{ format_currency($att->extra_duty_rate) }}
                        </div>
                        <div class="fw-bold text-dark">= {{ format_currency($att->extra_duty_amount) }}</div>
                        @if($att->extra_duty_job)
                        <small class="text-muted d-block"><i class="bi bi-tools me-1"></i>{{ $att->extra_duty_job }}</small>
                        @endif
                        @else
                        <span class="text-muted small">None</span>
                        @endif
                    </td>
                    <td data-label="Actions" style="min-width: 120px; text-align: right; padding-right: 1.5rem !important;">
                        <div class="action-btns justify-content-end" style="display: inline-flex; gap: 8px;">
                            @if(auth()->user()->hasPermission('attendance.edit'))
                            <button type="button" class="act-btn act-edit" onclick="editAttendance({{ json_encode($att) }})" title="Edit Attendance">
                                <i class="bi bi-pencil"></i>
                            </button>
                            @endif
                            @if(auth()->user()->hasPermission('attendance.delete'))
                            <button type="button" class="act-btn act-delete" onclick="deleteAttendance({{ $att->id }})" title="Delete Attendance">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="delete-att-form-{{ $att->id }}" action="{{ route('admin.attendance.destroy', $att) }}" method="POST" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <i class="bi bi-calendar-x display-4 d-block mb-2 text-muted opacity-50"></i>
                        No attendance records found for this date.
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-primary" onclick="openPunchModal()">
                                <i class="bi bi-plus-circle me-1"></i>Record Attendance Now
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($attendances->hasPages())
    <div class="card-footer bg-white py-3">
        {{ $attendances->links() }}
    </div>
    @endif
</div>

{{-- Attendance & Extra Duty Punch Modal --}}
<div class="modal fade" id="punchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="punchForm" method="POST" action="{{ route('admin.attendance.store') }}">
                @csrf
                <input type="hidden" name="_method" id="punchMethod" value="POST">

                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="punchModalTitle">
                        <i class="bi bi-clock-history me-2"></i>Record Daily Attendance & Extra Duty
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" id="punch_date" class="form-control" value="{{ $date }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Employee / Staff <span class="text-danger">*</span></label>
                            <select name="employee_id" id="punch_employee_id" class="form-select" required onchange="onEmployeeSelected(this.value)">
                                <option value="">-- Select Worker --</option>
                                @foreach($employees as $e)
                                <option value="{{ $e->id }}"
                                        data-extra-rate="{{ $e->extra_duty_rate }}"
                                        data-salary-type="{{ $e->salary_type }}"
                                        data-hours="{{ $e->daily_working_hours ?: 8 }}">
                                    {{ $e->name }} ({{ $e->employee_code }}) - {{ $e->salary_type_label }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Site / Job Location Name</label>
                            <input type="text" name="site_name" id="punch_site_name" class="form-control"
                                   placeholder="e.g. Anna Nagar Terrace Waterproofing Site" list="siteSuggestions">
                            <datalist id="siteSuggestions">
                                @foreach($customers as $c)
                                <option value="{{ $c->name }} Site">{{ $c->company_name ?: $c->name }}</option>
                                @endforeach
                            </datalist>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Client / Customer (Optional)</label>
                            <select name="customer_id" id="punch_customer_id" class="form-select">
                                <option value="">-- None --</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Punch In Time</label>
                            <input type="time" name="punch_in" id="punch_in" class="form-control" value="09:00">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Punch Out Time</label>
                            <input type="time" name="punch_out" id="punch_out" class="form-control" value="18:00">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select name="status" id="punch_status" class="form-select" required>
                                <option value="present">Present (Full Day)</option>
                                <option value="half_day">Half Day</option>
                                <option value="absent">Absent</option>
                                <option value="holiday">Holiday</option>
                                <option value="leave">Permission / Leave</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Regular Hours</label>
                            <input type="number" step="0.5" name="regular_hours" id="punch_regular_hours" class="form-control" value="8">
                        </div>

                        {{-- Extra Duty / Overtime Section --}}
                        <div class="col-12">
                            <div class="ot-card">
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="has_extra_duty" id="has_extra_duty" value="1" onchange="toggleExtraDuty(this.checked)">
                                    <label class="form-check-label fw-bold text-success" for="has_extra_duty">
                                        <i class="bi bi-clock me-1"></i>Worker did Extra Duty / Overtime (OT)
                                    </label>
                                </div>

                                <div id="extraDutyFields" style="display: none;">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Extra Hours</label>
                                            <input type="number" step="0.5" min="0" max="24" name="extra_duty_hours" id="extra_duty_hours" class="form-control" placeholder="e.g. 4" oninput="calculateOtAmount()">
                                            <small class="text-muted">e.g. 4 hrs</small>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label fw-semibold">Rate per Hour (₹)</label>
                                            <input type="number" step="0.01" min="0" name="extra_duty_rate" id="extra_duty_rate" class="form-control" placeholder="e.g. 100" oninput="calculateOtAmount()">
                                            <small class="text-muted">Job specific OT rate</small>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Job / Work Description</label>
                                            <input type="text" name="extra_duty_job" id="extra_duty_job" class="form-control" placeholder="e.g. Night crack injection grouting">
                                            <small class="text-muted">Work performed during OT</small>
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label fw-semibold text-success">OT Pay (₹)</label>
                                            <input type="text" id="extra_duty_amount_display" class="form-control bg-white fw-bold text-success border-success" readonly value="₹ 0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes / Remarks</label>
                            <input type="text" name="notes" id="punch_notes" class="form-control" placeholder="e.g. Completed 1st coat chemical coating">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4" id="savePunchBtn" data-no-loading="true">
                        <i class="bi bi-check-circle me-1"></i>Save Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let punchModalInstance;

document.addEventListener('DOMContentLoaded', function() {
    punchModalInstance = new bootstrap.Modal(document.getElementById('punchModal'));

    const punchForm = document.getElementById('punchForm');
    if (punchForm) {
        punchForm.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                return;
            }
            const btn = document.getElementById('savePunchBtn');
            if (btn) {
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
                btn.style.pointerEvents = 'none';
                setTimeout(() => {
                    btn.setAttribute('disabled', 'true');
                }, 50);
                setTimeout(() => {
                    btn.removeAttribute('disabled');
                    btn.style.pointerEvents = '';
                    btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Save Attendance';
                }, 6000);
            }
        });
    }
});

function openPunchModal() {
    document.getElementById('punchForm').reset();
    document.getElementById('punchForm').action = "{{ route('admin.attendance.store') }}";
    document.getElementById('punchMethod').value = "POST";
    document.getElementById('punchModalTitle').innerHTML = '<i class="bi bi-clock-history me-2"></i>Record Daily Attendance & Extra Duty';
    document.getElementById('punch_date').value = "{{ $date }}";
    document.getElementById('punch_in').value = "09:00";
    document.getElementById('punch_out').value = "18:00";
    document.getElementById('punch_regular_hours').value = "8";
    document.getElementById('has_extra_duty').checked = false;
    toggleExtraDuty(false);

    const btn = document.getElementById('savePunchBtn');
    if (btn) {
        btn.removeAttribute('disabled');
        btn.style.pointerEvents = '';
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Save Attendance';
    }

    punchModalInstance.show();
}

function onEmployeeSelected(empId) {
    if (!empId) return;
    const select = document.getElementById('punch_employee_id');
    const selectedOption = select.options[select.selectedIndex];
    const extraRate = selectedOption.getAttribute('data-extra-rate') || 0;
    const hours = selectedOption.getAttribute('data-hours') || 8;

    document.getElementById('extra_duty_rate').value = extraRate;
    document.getElementById('punch_regular_hours').value = hours;
    calculateOtAmount();
}

function toggleExtraDuty(enabled) {
    const fields = document.getElementById('extraDutyFields');
    if (enabled) {
        fields.style.display = 'block';
        calculateOtAmount();
    } else {
        fields.style.display = 'none';
        document.getElementById('extra_duty_hours').value = '';
        document.getElementById('extra_duty_amount_display').value = '₹ 0.00';
    }
}

function calculateOtAmount() {
    const hours = parseFloat(document.getElementById('extra_duty_hours').value) || 0;
    const rate = parseFloat(document.getElementById('extra_duty_rate').value) || 0;
    const amount = (hours * rate).toFixed(2);
    document.getElementById('extra_duty_amount_display').value = '₹ ' + Number(amount).toLocaleString('en-IN', { minimumFractionDigits: 2 });
}

function editAttendance(att) {
    document.getElementById('punchForm').reset();
    document.getElementById('punchForm').action = "{{ url('admin/attendance') }}/" + att.id;
    document.getElementById('punchMethod').value = "PUT";
    document.getElementById('punchModalTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Attendance & Extra Duty';

    document.getElementById('punch_date').value = att.date ? att.date.substring(0, 10) : '';
    document.getElementById('punch_employee_id').value = att.employee_id;
    document.getElementById('punch_site_name').value = att.site_name || '';
    document.getElementById('punch_customer_id').value = att.customer_id || '';
    document.getElementById('punch_in').value = att.punch_in ? att.punch_in.substring(0, 5) : '';
    document.getElementById('punch_out').value = att.punch_out ? att.punch_out.substring(0, 5) : '';
    document.getElementById('punch_status').value = att.status;
    document.getElementById('punch_regular_hours').value = att.regular_hours || 8;
    document.getElementById('punch_notes').value = att.notes || '';

    if (att.has_extra_duty) {
        document.getElementById('has_extra_duty').checked = true;
        document.getElementById('extra_duty_hours').value = att.extra_duty_hours;
        document.getElementById('extra_duty_rate').value = att.extra_duty_rate;
        document.getElementById('extra_duty_job').value = att.extra_duty_job || '';
        toggleExtraDuty(true);
    } else {
        document.getElementById('has_extra_duty').checked = false;
        toggleExtraDuty(false);
    }

    const btn = document.getElementById('savePunchBtn');
    if (btn) {
        btn.removeAttribute('disabled');
        btn.style.pointerEvents = '';
        btn.innerHTML = '<i class="bi bi-check-circle me-1"></i>Update Attendance';
    }

    punchModalInstance.show();
}

function deleteAttendance(id) {
    Swal.fire({
        title: 'Delete Attendance?',
        text: 'Are you sure you want to delete this attendance record?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete!'
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById(`delete-att-form-${id}`).submit();
        }
    });
}
</script>
@endpush
