<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AssetEmployee;
use App\Models\Attendance;
use App\Models\Payroll;
use App\Models\PayrollItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = Payroll::with(['employee', 'createdBy']);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('payroll_number', 'like', "%{$search}%")
                  ->orWhereHas('employee', fn($eq) => $eq->where('name', 'like', "%{$search}%")->orWhere('employee_code', 'like', "%{$search}%"));
            });
        }

        if ($empId = $request->employee_id) {
            $query->where('employee_id', $empId);
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($period = $request->period_type) {
            $query->where('period_type', $period);
        }

        if ($request->filled('from')) {
            $query->whereDate('start_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('end_date', '<=', $request->to);
        }

        $summary = [
            'total_disbursed'  => (clone $query)->where('status', 'paid')->sum('net_amount'),
            'approved_pending' => (clone $query)->where('status', 'approved')->sum('net_amount'),
            'draft_count'      => (clone $query)->where('status', 'draft')->count(),
            'total_extra_duty' => (clone $query)->sum('total_extra_duty_amount'),
        ];

        $payrolls = $query->latest('start_date')->latest()->paginate(15)->withQueryString();
        $employees = AssetEmployee::orderBy('name')->get();

        return view('admin.payrolls.index', compact('payrolls', 'summary', 'employees'));
    }

    public function create(Request $request)
    {
        // Default to current week (Monday to Saturday)
        $now = Carbon::now();
        $defaultStart = $now->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
        $defaultEnd   = $now->copy()->startOfWeek(Carbon::MONDAY)->addDays(5)->format('Y-m-d'); // 6th day: Saturday

        $employees = AssetEmployee::where('status', 'active')->orderBy('name')->get();
        $selectedEmployeeId = $request->input('employee_id');

        return view('admin.payrolls.create', compact('employees', 'defaultStart', 'defaultEnd', 'selectedEmployeeId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id'      => 'required|exists:asset_employees,id',
            'period_type'      => 'required|string|max:50',
            'start_date'       => 'required|date',
            'end_date'         => 'required|date|after_or_equal:start_date',
            'allowance_amount' => 'nullable|numeric|min:0',
            'allowance_notes'  => 'nullable|string|max:255',
            'deduction_amount' => 'nullable|numeric|min:0',
            'deduction_notes'  => 'nullable|string|max:255',
            'status'           => 'required|in:draft,approved,paid',
            'payment_method'   => 'nullable|string|max:50',
            'payment_date'     => 'nullable|date',
            'payment_reference'=> 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ]);

        $employee = AssetEmployee::findOrFail($request->employee_id);

        // Fetch attendances in period
        $attendances = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->orderBy('date')
            ->get();

        $daysPresent = $attendances->where('status', 'present')->count();
        $daysHalfDay = $attendances->where('status', 'half_day')->count();
        $daysAbsent  = $attendances->where('status', 'absent')->count();
        $totalWorkingDays = $daysPresent + ($daysHalfDay * 0.5);

        $totalExtraDutyHours  = (float) $attendances->sum('extra_duty_hours');
        $totalExtraDutyAmount = (float) $attendances->sum('extra_duty_amount');

        // Base salary calculations
        $baseSalaryRate = (float) $employee->base_salary;
        $baseSalaryEarned = 0;

        if ($request->period_type === 'weekly') {
            $dailyRate = $baseSalaryRate / 6;
            $baseSalaryEarned = round($dailyRate * $totalWorkingDays, 2);
        } elseif ($request->period_type === 'monthly') {
            $dailyRate = $baseSalaryRate / 30;
            $baseSalaryEarned = round($dailyRate * $totalWorkingDays, 2);
        } elseif (in_array($request->period_type, ['40_days', '60_days', '70_days', 'custom_days'])) {
            $cycleDays = max(1, (int) ($employee->settlement_days ?: 40));
            $dailyRate = $baseSalaryRate / $cycleDays;
            $baseSalaryEarned = round($dailyRate * $totalWorkingDays, 2);
        } else {
            $baseSalaryEarned = round($baseSalaryRate * $totalWorkingDays, 2);
        }

        $allowanceAmount = (float) ($request->allowance_amount ?? 0);
        $deductionAmount = (float) ($request->deduction_amount ?? 0);

        $gross = round($baseSalaryEarned + $totalExtraDutyAmount + $allowanceAmount, 2);
        $net = max(0, round($gross - $deductionAmount, 2));

        $payroll = DB::transaction(function () use ($request, $employee, $attendances, $daysPresent, $daysHalfDay, $daysAbsent, $totalWorkingDays, $baseSalaryRate, $baseSalaryEarned, $totalExtraDutyHours, $totalExtraDutyAmount, $allowanceAmount, $deductionAmount, $gross, $net) {
            $payroll = Payroll::create([
                'payroll_number'          => Payroll::generateNumber(),
                'employee_id'             => $employee->id,
                'period_type'             => $request->period_type,
                'start_date'              => $request->start_date,
                'end_date'                => $request->end_date,
                'days_present'            => $daysPresent,
                'days_half_day'           => $daysHalfDay,
                'days_absent'             => $daysAbsent,
                'total_working_days'      => $totalWorkingDays,
                'base_salary_rate'        => $baseSalaryRate,
                'base_salary_earned'      => $baseSalaryEarned,
                'total_extra_duty_hours'  => $totalExtraDutyHours,
                'total_extra_duty_amount' => $totalExtraDutyAmount,
                'allowance_amount'        => $allowanceAmount,
                'allowance_notes'         => $request->allowance_notes,
                'deduction_amount'        => $deductionAmount,
                'deduction_notes'         => $request->deduction_notes,
                'gross_amount'            => $gross,
                'net_amount'              => $net,
                'status'                  => $request->status,
                'payment_method'          => $request->payment_method,
                'payment_date'            => $request->status === 'paid' ? ($request->payment_date ?: Carbon::today()) : null,
                'payment_reference'       => $request->payment_reference,
                'notes'                   => $request->notes,
                'created_by'              => auth()->id(),
            ]);

            // Save snapshot items
            foreach ($attendances as $att) {
                PayrollItem::create([
                    'payroll_id'        => $payroll->id,
                    'attendance_id'     => $att->id,
                    'date'              => $att->date,
                    'site_name'         => $att->site_name ?? 'Regular Site',
                    'punch_in'          => $att->punch_in,
                    'punch_out'         => $att->punch_out,
                    'status'            => $att->status,
                    'regular_hours'     => (float) $att->regular_hours,
                    'extra_duty_hours'  => (float) $att->extra_duty_hours,
                    'extra_duty_rate'   => (float) $att->extra_duty_rate,
                    'extra_duty_job'    => $att->extra_duty_job,
                    'extra_duty_amount' => (float) $att->extra_duty_amount,
                    'notes'             => $att->notes,
                ]);
            }

            return $payroll;
        });

        ActivityLog::log('CREATE', 'Payroll', "Generated payroll {$payroll->payroll_number} for {$employee->name} (Net: {$net})", $payroll);

        return redirect()->route('admin.payrolls.show', $payroll)->with('success', "Payroll {$payroll->payroll_number} processed successfully!");
    }

    public function show(Payroll $payroll)
    {
        $payroll->load(['employee', 'items.attendance', 'createdBy']);
        return view('admin.payrolls.show', compact('payroll'));
    }

    public function edit(Payroll $payroll)
    {
        $payroll->load(['employee', 'items']);
        return view('admin.payrolls.edit', compact('payroll'));
    }

    public function update(Request $request, Payroll $payroll)
    {
        $request->validate([
            'allowance_amount'  => 'nullable|numeric|min:0',
            'allowance_notes'   => 'nullable|string|max:255',
            'deduction_amount'  => 'nullable|numeric|min:0',
            'deduction_notes'   => 'nullable|string|max:255',
            'status'            => 'required|in:draft,approved,paid',
            'payment_method'    => 'nullable|string|max:50',
            'payment_date'      => 'nullable|date',
            'payment_reference' => 'nullable|string|max:100',
            'notes'             => 'nullable|string',
        ]);

        $allowance = (float) ($request->allowance_amount ?? 0);
        $deduction = (float) ($request->deduction_amount ?? 0);

        $gross = round($payroll->base_salary_earned + $payroll->total_extra_duty_amount + $allowance, 2);
        $net = max(0, round($gross - $deduction, 2));

        $payroll->update([
            'allowance_amount'  => $allowance,
            'allowance_notes'   => $request->allowance_notes,
            'deduction_amount'  => $deduction,
            'deduction_notes'   => $request->deduction_notes,
            'gross_amount'      => $gross,
            'net_amount'        => $net,
            'status'            => $request->status,
            'payment_method'    => $request->payment_method,
            'payment_date'      => $request->status === 'paid' ? ($request->payment_date ?: Carbon::today()) : null,
            'payment_reference' => $request->payment_reference,
            'notes'             => $request->notes,
        ]);

        ActivityLog::log('UPDATE', 'Payroll', "Updated payroll {$payroll->payroll_number}", $payroll);

        return redirect()->route('admin.payrolls.show', $payroll)->with('success', "Payroll {$payroll->payroll_number} updated successfully!");
    }

    public function updateStatus(Request $request, Payroll $payroll)
    {
        $request->validate([
            'status'            => 'required|in:draft,approved,paid',
            'payment_method'    => 'nullable|string|max:50',
            'payment_date'      => 'nullable|date',
            'payment_reference' => 'nullable|string|max:100',
        ]);

        $payroll->update([
            'status'            => $request->status,
            'payment_method'    => $request->payment_method,
            'payment_date'      => $request->status === 'paid' ? ($request->payment_date ?: Carbon::today()) : null,
            'payment_reference' => $request->payment_reference,
        ]);

        ActivityLog::log('UPDATE', 'Payroll', "Changed status of {$payroll->payroll_number} to {$payroll->status}", $payroll);

        return redirect()->back()->with('success', "Payroll status updated to " . ucfirst($payroll->status));
    }

    public function print(Payroll $payroll)
    {
        $payroll->load(['employee', 'items']);
        return view('admin.payrolls.print', compact('payroll'));
    }

    public function pdfDownload(Payroll $payroll)
    {
        $payroll->load(['employee', 'items']);

        $pdf = Pdf::loadView('admin.payrolls.pdf', compact('payroll'))
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true);

        $filename = "Payslip-{$payroll->payroll_number}-" . str_replace(' ', '_', $payroll->employee?->name ?? 'Staff') . ".pdf";

        return $pdf->download($filename);
    }

    public function destroy(Payroll $payroll)
    {
        $num = $payroll->payroll_number;
        $payroll->delete();

        ActivityLog::log('DELETE', 'Payroll', "Deleted payroll {$num}", $payroll);

        return redirect()->route('admin.payrolls.index')->with('success', "Payroll {$num} deleted successfully.");
    }
}
