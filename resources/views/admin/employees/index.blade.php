@extends('layouts.app')
@section('title', 'Employees & Salary Schemes Directory')

@push('styles')
<style>
.emp-stat {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    padding: 16px 18px;
    height: 100%;
    transition: transform .15s ease;
}
.emp-stat:hover { transform: translateY(-2px); }
.emp-label {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .6px;
    color: #94a3b8;
    margin-bottom: 4px;
}
.emp-value {
    font-size: 22px;
    font-weight: 850;
    color: #0f172a;
}
.emp-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.scheme-nav {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
}
.scheme-pill {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    color: #475569;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    white-space: nowrap;
    transition: all .15s ease;
}
.scheme-pill:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.scheme-pill.active {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title">Employees & Wage Scheme Directory</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Employees</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.attendance.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-calendar-check me-1"></i>Daily Attendance & OT
        </a>
        <a href="{{ route('admin.payrolls.index') }}" class="btn btn-outline-success">
            <i class="bi bi-cash-coin me-1"></i>Process Payroll
        </a>
        @if(auth()->user()->hasPermission('employees.create'))
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i>Add Employee
        </a>
        @endif
    </div>
</div>

{{-- KPI Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md">
        <div class="emp-stat">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="emp-label">Total Staff</div>
                    <div class="emp-value">{{ $summary['total'] }}</div>
                </div>
                <div class="emp-icon" style="background:#e0f2fe;color:#0284c7;">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="emp-stat">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="emp-label">Weekly (6D)</div>
                    <div class="emp-value" style="color:#4338ca;">{{ $summary['weekly'] }}</div>
                </div>
                <div class="emp-icon" style="background:#e0e7ff;color:#4338ca;">
                    <i class="bi bi-calendar3-week"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="emp-stat">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="emp-label">Monthly</div>
                    <div class="emp-value" style="color:#b45309;">{{ $summary['monthly'] }}</div>
                </div>
                <div class="emp-icon" style="background:#fef3c7;color:#b45309;">
                    <i class="bi bi-calendar3"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="emp-stat">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="emp-label">40 / 60 / 70 Days</div>
                    <div class="emp-value" style="color:#7e22ce;">{{ $summary['long_interval'] }}</div>
                </div>
                <div class="emp-icon" style="background:#f3e8ff;color:#7e22ce;">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md">
        <div class="emp-stat">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="emp-label">Daily / Flexible</div>
                    <div class="emp-value" style="color:#15803d;">{{ $summary['daily_ondemand'] }}</div>
                </div>
                <div class="emp-icon" style="background:#dcfce7;color:#15803d;">
                    <i class="bi bi-lightning-charge"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Salary Scheme Quick Filter Tabs --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <div class="scheme-nav">
            <a href="{{ route('admin.employees.index') }}" class="scheme-pill {{ !request('salary_type') ? 'active' : '' }}">
                All Staff ({{ $summary['total'] }})
            </a>
            <a href="{{ route('admin.employees.index', ['salary_type' => 'weekly']) }}" class="scheme-pill {{ request('salary_type') == 'weekly' ? 'active' : '' }}">
                <i class="bi bi-calendar3-week me-1 text-primary"></i>Weekly Wages ({{ $summary['weekly'] }})
            </a>
            <a href="{{ route('admin.employees.index', ['salary_type' => 'monthly']) }}" class="scheme-pill {{ request('salary_type') == 'monthly' ? 'active' : '' }}">
                <i class="bi bi-calendar3 me-1 text-warning"></i>Monthly Salary ({{ $summary['monthly'] }})
            </a>
            <a href="{{ route('admin.employees.index', ['salary_type' => '40_days']) }}" class="scheme-pill {{ request('salary_type') == '40_days' ? 'active' : '' }}">
                <i class="bi bi-hourglass-split me-1 text-info"></i>40 Days Cycle ({{ $summary['forty_days'] }})
            </a>
            <a href="{{ route('admin.employees.index', ['salary_type' => '60_days']) }}" class="scheme-pill {{ request('salary_type') == '60_days' ? 'active' : '' }}">
                <i class="bi bi-hourglass me-1" style="color:#7e22ce;"></i>60 Days Cycle ({{ $summary['sixty_days'] }})
            </a>
            <a href="{{ route('admin.employees.index', ['salary_type' => '70_days']) }}" class="scheme-pill {{ request('salary_type') == '70_days' ? 'active' : '' }}">
                <i class="bi bi-clock me-1" style="color:#86198f;"></i>70 Days Cycle ({{ $summary['seventy_days'] }})
            </a>
            <a href="{{ route('admin.employees.index', ['salary_type' => 'custom_days']) }}" class="scheme-pill {{ request('salary_type') == 'custom_days' ? 'active' : '' }}">
                <i class="bi bi-sliders me-1" style="color:#0284c7;"></i>Custom Interval ({{ $summary['custom_days'] }})
            </a>
            <a href="{{ route('admin.employees.index', ['salary_type' => 'daily_ondemand']) }}" class="scheme-pill {{ request('salary_type') == 'daily_ondemand' ? 'active' : '' }}">
                <i class="bi bi-lightning-charge me-1 text-success"></i>Daily / Flexible ({{ $summary['daily_ondemand'] }})
            </a>
        </div>
    </div>
</div>

{{-- Search & Employee List --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <form method="GET" action="{{ route('admin.employees.index') }}" class="d-flex flex-wrap align-items-center gap-2">
            <div class="flex-grow-1" style="min-width: 240px;">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, code, mobile, designation..." value="{{ request('search') }}">
                </div>
            </div>
            <div style="min-width: 220px; flex: 0 1 auto;">
                <select name="salary_type" class="form-select">
                    <option value="">All Salary Schemes</option>
                    <option value="weekly" {{ request('salary_type') == 'weekly' ? 'selected' : '' }}>Weekly Wages (6 Days)</option>
                    <option value="monthly" {{ request('salary_type') == 'monthly' ? 'selected' : '' }}>Monthly Salary (30 Days)</option>
                    <option value="40_days" {{ request('salary_type') == '40_days' ? 'selected' : '' }}>40 Days Settlement</option>
                    <option value="60_days" {{ request('salary_type') == '60_days' ? 'selected' : '' }}>60 Days Settlement</option>
                    <option value="70_days" {{ request('salary_type') == '70_days' ? 'selected' : '' }}>70 Days Settlement</option>
                    <option value="custom_days" {{ request('salary_type') == 'custom_days' ? 'selected' : '' }}>Custom Days Settlement</option>
                    <option value="daily_ondemand" {{ request('salary_type') == 'daily_ondemand' ? 'selected' : '' }}>Daily / Flexible</option>
                </select>
            </div>
            <div style="min-width: 140px; flex: 0 1 auto;">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="submit" class="btn btn-outline-primary px-3">
                    <i class="bi bi-funnel me-1"></i>Filter
                </button>
                @if(request()->filled('search') || request()->filled('salary_type') || (request()->filled('status') && request('status') !== 'all'))
                <a href="{{ route('admin.employees.index') }}" class="btn btn-outline-secondary px-2 d-inline-flex align-items-center gap-1" title="Clear All Filters">
                    <i class="bi bi-x-circle"></i>
                    <span>Clear</span>
                </a>
                @endif
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 100px;">Code</th>
                    <th>Worker Name & Role</th>
                    <th>Salary Scheme / Cycle</th>
                    <th>Base Rate & Equivalent</th>
                    <th>Extra Duty (OT)</th>
                    <th>Last Salary Settled</th>
                    <th>Status</th>
                    <th style="width: 160px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                @php
                    $cycleDays = match($emp->salary_type) {
                        'weekly'         => 6,
                        'monthly'        => 30,
                        '40_days'        => 40,
                        '60_days'        => 60,
                        '70_days'        => 70,
                        'custom_days'    => ($emp->settlement_days ?: 40),
                        default          => 1,
                    };
                    $dailyEq = $cycleDays > 0 ? round($emp->base_salary / $cycleDays, 2) : $emp->base_salary;
                @endphp
                <tr>
                    <td>
                        <span class="badge bg-light text-dark font-monospace border">{{ $emp->employee_code }}</span>
                    </td>
                    <td>
                        <div class="fw-bold text-dark fs-6">{{ $emp->name }}</div>
                        <div class="text-secondary small">{{ $emp->designation ?: 'Site Technician' }}</div>
                        @if($emp->mobile)
                        <div class="text-muted small"><i class="bi bi-telephone me-1"></i>{{ $emp->mobile }}</div>
                        @endif
                    </td>
                    <td>
                        <div>{!! $emp->cycle_badge !!}</div>
                        <small class="text-muted d-block mt-1">
                            @if($emp->salary_type === 'weekly')
                                Settle every Saturday (6 Days)
                            @elseif($emp->salary_type === 'monthly')
                                Monthly (30 Days)
                            @elseif(in_array($emp->salary_type, ['40_days', '60_days', '70_days', 'custom_days']))
                                Settle every {{ $cycleDays }} Days
                            @else
                                On-Demand Payout
                            @endif
                        </small>
                    </td>
                    <td>
                        <div class="fw-bold text-dark fs-6">{{ format_currency($emp->base_salary) }}</div>
                        <span class="text-muted small">
                            @if($emp->salary_type === 'daily_ondemand')
                                / working day
                            @else
                                / {{ $cycleDays }} days ({{ format_currency($dailyEq) }}/day)
                            @endif
                        </span>
                    </td>
                    <td>
                        @if($emp->extra_duty_rate > 0)
                        <div class="fw-semibold text-primary">{{ format_currency($emp->extra_duty_rate) }} <span class="text-muted small">/ hr</span></div>
                        @else
                        <span class="text-muted small">Not set</span>
                        @endif
                    </td>
                    <td>
                        @if($emp->latestPayroll)
                        <div class="small fw-semibold text-success">
                            <i class="bi bi-check-circle me-1"></i>{{ $emp->latestPayroll->end_date->format('d M Y') }}
                        </div>
                        <small class="text-muted font-monospace">{{ $emp->latestPayroll->payroll_number }}</small>
                        @else
                        <span class="text-muted small">No payroll yet</span>
                        @endif
                    </td>
                    <td>
                        {!! $emp->status_badge !!}
                    </td>
                    <td data-label="Actions">
                        <div class="action-btns">
                            {{-- Attendance Log --}}
                            <a href="{{ route('admin.attendance.index', ['employee_id' => $emp->id]) }}" class="act-btn act-view" title="Attendance Log">
                                <i class="bi bi-calendar-check"></i>
                            </a>

                            {{-- Process Payroll / Wage --}}
                            <a href="{{ route('admin.payrolls.create', ['employee_id' => $emp->id]) }}" class="act-btn act-pay" title="Process Wage Sheet">
                                <i class="bi bi-cash-coin"></i>
                            </a>

                            {{-- Edit Employee --}}
                            @if(auth()->user()->hasPermission('employees.edit'))
                            <a href="{{ route('admin.employees.edit', $emp) }}" class="act-btn act-edit" title="Edit Employee">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif

                            {{-- Delete / Deactivate --}}
                            @if(auth()->user()->hasPermission('employees.delete'))
                            <button type="button" onclick="deleteEmployee({{ $emp->id }}, '{{ addslashes($emp->name) }}')" class="act-btn act-delete" title="Delete Employee">
                                <i class="bi bi-trash"></i>
                            </button>
                            <form id="delete-emp-form-{{ $emp->id }}" action="{{ route('admin.employees.destroy', $emp) }}" method="POST" style="display:none;">
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
                        <i class="bi bi-people display-4 d-block mb-2 text-muted opacity-50"></i>
                        No employees found matching the criteria.
                        <div class="mt-2">
                            <a href="{{ route('admin.employees.create') }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Add First Employee
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($employees->hasPages())
    <div class="card-footer bg-white py-3">
        {{ $employees->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function deleteEmployee(id, name) {
    Swal.fire({
        title: 'Delete Employee?',
        text: `Are you sure you want to delete ${name}? If the employee has past attendance or payroll records, they will be marked Inactive instead.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Yes, delete!'
    }).then(r => {
        if (r.isConfirmed) {
            document.getElementById(`delete-emp-form-${id}`).submit();
        }
    });
}
</script>
@endpush
