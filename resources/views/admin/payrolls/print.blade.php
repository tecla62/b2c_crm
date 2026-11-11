<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Print Wage Sheet - {{ $payroll->payroll_number }}</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<style>
@media print {
    .no-print { display: none !important; }
    body { padding: 0; font-size: 12px; }
}
body { background: #f8fafc; color: #0f172a; padding: 20px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
.print-sheet { background: #fff; max-width: 900px; margin: 0 auto; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,.08); border-radius: 8px; }
.section-head { font-size: 13px; font-weight: 700; text-transform: uppercase; border-bottom: 2px solid #0f172a; padding-bottom: 4px; margin-bottom: 12px; margin-top: 20px; }
</style>
</head>
<body>

<div class="no-print text-center mb-3">
    <button onclick="window.print()" class="btn btn-primary btn-lg me-2"><i class="bi bi-printer me-1"></i>Print Now</button>
    <button onclick="window.close()" class="btn btn-outline-secondary btn-lg">Close Window</button>
</div>

<div class="print-sheet">
    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
        <div>
            <h3 class="fw-bold mb-1">{{ setting('company_name', 'Waterproofing CRM') }}</h3>
            <div class="text-muted small">
                {{ setting('company_address') }}<br>
                Phone: {{ setting('company_mobile') }} | Email: {{ setting('company_email') }}
            </div>
        </div>
        <div class="text-end">
            <span class="badge bg-dark fs-6 px-3 py-1.5">{{ strtoupper($payroll->period_type) }} WAGE SHEET</span>
            <div class="fs-5 fw-bold font-monospace mt-1">{{ $payroll->payroll_number }}</div>
            <div class="text-muted small">
                Period: <strong>{{ $payroll->start_date->format('d/m/Y') }} &mdash; {{ $payroll->end_date->format('d/m/Y') }}</strong><br>
                Status: <strong>{{ strtoupper($payroll->status) }}</strong>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6">
            <div class="p-3 bg-light rounded">
                <div class="small fw-bold text-muted text-uppercase mb-2">Employee Details</div>
                <div class="fw-bold fs-6">{{ $payroll->employee?->name }}</div>
                <div class="small text-muted">Code: {{ $payroll->employee?->employee_code }} | {{ $payroll->employee?->designation }}</div>
                <div class="small text-muted">Salary Scheme: {{ $payroll->employee?->salary_type_label }}</div>
                <div class="small text-muted">Phone: {{ $payroll->employee?->mobile }}</div>
            </div>
        </div>
        <div class="col-6">
            <div class="p-3 bg-light rounded">
                <div class="small fw-bold text-muted text-uppercase mb-2">Payment Details</div>
                <div>Method: <strong>{{ $payroll->payment_method ?: 'Cash' }}</strong></div>
                @if($payroll->payment_date)
                <div>Date: <strong>{{ $payroll->payment_date->format('d/m/Y') }}</strong></div>
                @endif
                <div>Bank: <strong>{{ $payroll->employee?->bank_name ?: 'Cash' }}</strong></div>
                @if($payroll->employee?->account_number)
                <div>A/C: <span class="font-monospace">{{ $payroll->employee?->account_number }}</span></div>
                @endif
            </div>
        </div>
    </div>

    <div class="section-head">Site Attendance & Extra Duty (OT) Breakdown</div>
    <table class="table table-bordered table-sm align-middle mb-3">
        <thead class="table-light">
            <tr>
                <th>Date & Day</th>
                <th>Site / Job Location</th>
                <th>Shift</th>
                <th>Status</th>
                <th>Regular Hrs</th>
                <th>Extra Duty (OT) Details</th>
                <th class="text-end">OT Pay (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payroll->items as $item)
            <tr>
                <td><strong>{{ $item->date->format('d/m/Y') }}</strong> ({{ $item->date->format('D') }})</td>
                <td>{{ $item->site_name ?: 'Main Site' }}</td>
                <td>{{ $item->punch_in ? \Carbon\Carbon::parse($item->punch_in)->format('h:i A') : '--' }} - {{ $item->punch_out ? \Carbon\Carbon::parse($item->punch_out)->format('h:i A') : '--' }}</td>
                <td>{{ ucfirst($item->status) }}</td>
                <td>{{ $item->regular_hours }}</td>
                <td>
                    @if($item->extra_duty_hours > 0)
                    <strong>{{ $item->extra_duty_hours }} hrs &times; ₹{{ $item->extra_duty_rate }}</strong> ({{ $item->extra_duty_job }})
                    @else
                    &mdash;
                    @endif
                </td>
                <td class="text-end">₹ {{ number_format($item->extra_duty_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="table-light fw-bold">
            <tr>
                <td colspan="5" class="text-end">Total Extra Duty (OT):</td>
                <td>{{ $payroll->total_extra_duty_hours }} Hours</td>
                <td class="text-end text-success">₹ {{ number_format($payroll->total_extra_duty_amount, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="row justify-content-end mb-4">
        <div class="col-6">
            <table class="table table-sm table-bordered">
                <tr>
                    <td>Total Working Days:</td>
                    <td class="text-end"><strong>{{ $payroll->total_working_days }} Days</strong></td>
                </tr>
                <tr>
                    <td>Base Wages Earned:</td>
                    <td class="text-end">₹ {{ number_format($payroll->base_salary_earned, 2) }}</td>
                </tr>
                <tr>
                    <td>Extra Duty OT Pay:</td>
                    <td class="text-end text-success">+ ₹ {{ number_format($payroll->total_extra_duty_amount, 2) }}</td>
                </tr>
                @if($payroll->allowance_amount > 0)
                <tr>
                    <td>Allowance:</td>
                    <td class="text-end text-success">+ ₹ {{ number_format($payroll->allowance_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="table-light fw-bold">
                    <td>Gross Pay:</td>
                    <td class="text-end">₹ {{ number_format($payroll->gross_amount, 2) }}</td>
                </tr>
                @if($payroll->deduction_amount > 0)
                <tr>
                    <td>Deductions / Advances:</td>
                    <td class="text-end text-danger">- ₹ {{ number_format($payroll->deduction_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="table-dark fw-bold fs-6">
                    <td>NET PAYABLE:</td>
                    <td class="text-end">₹ {{ number_format($payroll->net_amount, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row pt-5 mt-4 text-center">
        <div class="col-6">
            <div style="border-top: 1px solid #94a3b8; width: 80%; margin: 0 auto; padding-top: 5px;">
                <strong>Worker Signature</strong><br>
                <small class="text-muted">{{ $payroll->employee?->name }}</small>
            </div>
        </div>
        <div class="col-6">
            <div style="border-top: 1px solid #94a3b8; width: 80%; margin: 0 auto; padding-top: 5px;">
                <strong>Authorized Signatory</strong><br>
                <small class="text-muted">{{ setting('company_name') }}</small>
            </div>
        </div>
    </div>
</div>

<script>
window.onload = function() {
    // Optionally trigger print automatically if opened
};
</script>
</body>
</html>
