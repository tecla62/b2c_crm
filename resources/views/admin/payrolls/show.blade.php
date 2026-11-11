@extends('layouts.app')
@section('title', 'Payroll Slip - ' . $payroll->payroll_number)

@push('styles')
<style>
.payslip-container {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
    overflow: hidden;
}
.payslip-header {
    background: linear-gradient(135deg, #1e293b, #0f172a);
    color: #fff;
    padding: 30px;
}
.summary-chip {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px 16px;
}
.summary-chip-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 2px;
}
.summary-chip-val {
    font-size: 20px;
    font-weight: 850;
    color: #0f172a;
}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h1 class="page-title">Weekly Wage / Salary Slip</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.payrolls.index') }}">Payroll</a></li>
                <li class="breadcrumb-item active">{{ $payroll->payroll_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.payrolls.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back
        </a>
        <a href="{{ route('admin.payrolls.print', $payroll) }}" target="_blank" class="btn btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>Print
        </a>
        <a href="{{ route('admin.payrolls.pdf', $payroll) }}" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf me-1"></i>Download PDF
        </a>
        @if($payroll->status !== 'paid')
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#markPaidModal">
            <i class="bi bi-check2-circle me-1"></i>Mark as Paid
        </button>
        @endif
        @if(auth()->user()->hasPermission('payroll.edit'))
        <a href="{{ route('admin.payrolls.edit', $payroll) }}" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
        </a>
        @endif
    </div>
</div>

<div class="payslip-container mb-4">
    {{-- Payslip Header --}}
    <div class="payslip-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            @php
                $diffDays = $payroll->start_date->diffInDays($payroll->end_date) + 1;
                $cycleTitle = match($payroll->period_type) {
                    'weekly'         => 'WEEKLY WAGE SHEET (6 DAYS)',
                    'monthly'        => 'MONTHLY SALARY SHEET',
                    '40_days'        => '40-DAY SALARY SETTLEMENT SHEET',
                    '60_days'        => '60-DAY SALARY SETTLEMENT SHEET',
                    '70_days'        => '70-DAY SALARY SETTLEMENT SHEET',
                    'custom_days'    => "{$diffDays}-DAY SALARY SETTLEMENT SHEET",
                    'daily_ondemand' => 'DAILY / FLEXIBLE WAGE SHEET',
                    default          => strtoupper(str_replace('_', ' ', $payroll->period_type)) . ' WAGE SHEET',
                };
            @endphp
            <span class="badge bg-primary px-3 py-1.5 mb-2">{{ $cycleTitle }}</span>
            <h2 class="fw-bold mb-1">{{ $payroll->payroll_number }}</h2>
            <div class="text-white-50">
                Period: <strong class="text-white">{{ $payroll->start_date->format('d M Y') }}</strong> to <strong class="text-white">{{ $payroll->end_date->format('d M Y') }}</strong>
                ({{ $diffDays }} Days Interval)
            </div>
        </div>
        <div class="text-end">
            <div class="fs-6 text-white-50 mb-1">Status:</div>
            <div>{!! $payroll->status_badge !!}</div>
            @if($payroll->status === 'paid' && $payroll->payment_date)
            <div class="small text-white-50 mt-1">Paid on {{ $payroll->payment_date->format('d/m/Y') }} via {{ $payroll->payment_method ?: 'Cash' }}</div>
            @endif
        </div>
    </div>

    {{-- Details & Summary --}}
    <div class="p-4">
        {{-- Employee & Bank Details --}}
        <div class="row g-4 mb-4">
            <div class="col-12 col-md-6">
                <div class="card h-100 bg-light border-0">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-fill text-primary me-2"></i>Worker Information</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width: 140px;">Name:</th>
                                <td class="fw-bold text-dark">{{ $payroll->employee?->name ?? 'Staff' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Employee Code:</th>
                                <td><span class="badge bg-secondary font-monospace">{{ $payroll->employee?->employee_code }}</span></td>
                            </tr>
                            <tr>
                                <th class="text-muted">Designation:</th>
                                <td>{{ $payroll->employee?->designation ?: 'Site Technician' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Mobile:</th>
                                <td>{{ $payroll->employee?->mobile ?: 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Salary Scheme:</th>
                                <td><span class="fw-semibold text-primary">{{ $payroll->employee?->salary_type_label }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card h-100 bg-light border-0">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-bank text-success me-2"></i>Bank & Disbursement Details</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <th class="text-muted" style="width: 140px;">Bank Name:</th>
                                <td class="fw-semibold">{{ $payroll->employee?->bank_name ?: 'Direct Cash Payment' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Account Number:</th>
                                <td class="font-monospace">{{ $payroll->employee?->account_number ?: '---' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">IFSC Code:</th>
                                <td class="font-monospace text-uppercase">{{ $payroll->employee?->ifsc_code ?: '---' }}</td>
                            </tr>
                            @if($payroll->status === 'paid')
                            <tr>
                                <th class="text-muted">Payment Mode:</th>
                                <td class="fw-bold text-success">{{ $payroll->payment_method ?: 'Cash' }}</td>
                            </tr>
                            <tr>
                                <th class="text-muted">Reference #:</th>
                                <td>{{ $payroll->payment_reference ?: 'N/A' }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Row --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="summary-chip">
                    <div class="summary-chip-label">Working Days</div>
                    <div class="summary-chip-val text-primary">{{ $payroll->total_working_days }} days</div>
                    <small class="text-muted">{{ $payroll->days_present }} Present, {{ $payroll->days_half_day }} Half</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="summary-chip">
                    <div class="summary-chip-label">Base Wages Earned</div>
                    <div class="summary-chip-val text-dark">{{ format_currency($payroll->base_salary_earned) }}</div>
                    <small class="text-muted">Rate: {{ format_currency($payroll->base_salary_rate) }}</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="summary-chip" style="border-left: 3px solid #10b981;">
                    <div class="summary-chip-label text-success">Extra Duty (OT) Total</div>
                    <div class="summary-chip-val text-success">{{ format_currency($payroll->total_extra_duty_amount) }}</div>
                    <small class="text-muted">{{ $payroll->total_extra_duty_hours }} hrs Overtime</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="summary-chip" style="background:#eef2ff;border-color:#c7d2fe;">
                    <div class="summary-chip-label text-primary">Net Payable Amount</div>
                    <div class="summary-chip-val text-primary">{{ format_currency($payroll->net_amount) }}</div>
                    <small class="text-muted">Gross: {{ format_currency($payroll->gross_amount) }}</small>
                </div>
            </div>
        </div>

        {{-- Site Attendance Items Breakdown Table --}}
        <h5 class="fw-bold mb-3 text-dark">
            <i class="bi bi-geo-alt-fill text-danger me-2"></i>Site Attendance & Extra Duty Details (6-Day Breakdown)
        </h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">Date & Day</th>
                        <th>Site / Job Location</th>
                        <th>In / Out Shift</th>
                        <th>Regular Hrs</th>
                        <th>Status</th>
                        <th>Extra Duty (OT) Details</th>
                        <th class="text-end" style="width: 140px;">OT Pay (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payroll->items as $item)
                    <tr>
                        <td>
                            <div class="fw-bold">{{ $item->date->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $item->date->format('l') }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold text-primary"><i class="bi bi-building me-1"></i>{{ $item->site_name ?: 'Main Site' }}</div>
                            @if($item->notes)
                            <small class="text-muted">{{ $item->notes }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="small">{{ $item->punch_in ? \Carbon\Carbon::parse($item->punch_in)->format('h:i A') : '--' }} - {{ $item->punch_out ? \Carbon\Carbon::parse($item->punch_out)->format('h:i A') : '--' }}</span>
                        </td>
                        <td><strong>{{ $item->regular_hours }}</strong> hrs</td>
                        <td>
                            @if($item->status === 'present')
                            <span class="badge bg-success">Present</span>
                            @elseif($item->status === 'half_day')
                            <span class="badge bg-warning text-dark">Half Day</span>
                            @elseif($item->status === 'absent')
                            <span class="badge bg-danger">Absent</span>
                            @else
                            <span class="badge bg-light text-dark">{{ ucfirst($item->status) }}</span>
                            @endif
                        </td>
                        <td>
                            @if($item->extra_duty_hours > 0)
                            <div class="text-success fw-bold">
                                <i class="bi bi-clock me-1"></i>{{ $item->extra_duty_hours }} hrs &times; {{ format_currency($item->extra_duty_rate) }}
                            </div>
                            <small class="text-muted">{{ $item->extra_duty_job ?: 'Extra Duty Work' }}</small>
                            @else
                            <span class="text-muted small">&mdash;</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if($item->extra_duty_amount > 0)
                            <strong class="text-success">{{ format_currency($item->extra_duty_amount) }}</strong>
                            @else
                            <span class="text-muted">₹ 0.00</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No itemized attendance records attached.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="text-end">Total Extra Duty Pay:</th>
                        <th class="text-success">{{ $payroll->total_extra_duty_hours }} hrs</th>
                        <th class="text-end text-success fs-6">{{ format_currency($payroll->total_extra_duty_amount) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Final Wage Calculations Breakdown --}}
        <div class="row justify-content-end">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="card border-0 bg-light">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Base Wages Earned:</span>
                            <strong class="text-dark">{{ format_currency($payroll->base_salary_earned) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Extra Duty (OT) Amount:</span>
                            <strong class="text-success">+ {{ format_currency($payroll->total_extra_duty_amount) }}</strong>
                        </div>
                        @if($payroll->allowance_amount > 0)
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-muted">Allowance ({{ $payroll->allowance_notes ?: 'Bonus/Incentive' }}):</span>
                            <strong class="text-success">+ {{ format_currency($payroll->allowance_amount) }}</strong>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="fw-bold">Gross Earnings:</span>
                            <strong class="fw-bold text-dark">{{ format_currency($payroll->gross_amount) }}</strong>
                        </div>
                        @if($payroll->deduction_amount > 0)
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-danger">Deduction ({{ $payroll->deduction_notes ?: 'Advance recovery' }}):</span>
                            <strong class="text-danger">- {{ format_currency($payroll->deduction_amount) }}</strong>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between pt-2 fs-5">
                            <span class="fw-bold text-dark">Net Payable Salary:</span>
                            <span class="fw-bold text-primary">{{ format_currency($payroll->net_amount) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($payroll->notes)
        <div class="mt-4 p-3 bg-light rounded">
            <strong>Notes / Remarks:</strong>
            <p class="mb-0 text-muted mt-1">{{ $payroll->notes }}</p>
        </div>
        @endif
    </div>
</div>

{{-- Modal for Mark as Paid --}}
<div class="modal fade" id="markPaidModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form method="POST" action="{{ route('admin.payrolls.status', $payroll) }}">
                @csrf
                <input type="hidden" name="status" value="paid">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">Confirm Salary Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <p>You are marking <strong>{{ $payroll->payroll_number }}</strong> for <strong>{{ $payroll->employee?->name }}</strong> as Paid.</p>
                    <div class="alert alert-success fs-5 fw-bold text-center mb-3">
                        Total Payout: {{ format_currency($payroll->net_amount) }}
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
                        <label class="form-label fw-semibold">Reference / Voucher No</label>
                        <input type="text" name="payment_reference" class="form-control" placeholder="e.g. Bank UTR / UPI Ref">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
