<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AssetEmployee;
use App\Models\Attendance;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));
        $fromDate = $request->input('from', $date);
        $toDate = $request->input('to', $date);

        $query = Attendance::with(['employee', 'customer', 'createdBy']);

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('date', [$request->from, $request->to]);
        } elseif ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        } else {
            $query->whereDate('date', Carbon::today());
        }

        if ($empId = $request->employee_id) {
            $query->where('employee_id', $empId);
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        if ($site = $request->site_name) {
            $query->where('site_name', 'like', "%{$site}%");
        }

        // Summary calculations
        $summaryQuery = clone $query;
        $summary = [
            'total_punches'      => (clone $summaryQuery)->count(),
            'present'            => (clone $summaryQuery)->where('status', 'present')->count(),
            'half_day'           => (clone $summaryQuery)->where('status', 'half_day')->count(),
            'absent'             => (clone $summaryQuery)->where('status', 'absent')->count(),
            'total_extra_hours'  => (clone $summaryQuery)->where('has_extra_duty', true)->sum('extra_duty_hours'),
            'total_extra_amount' => (clone $summaryQuery)->where('has_extra_duty', true)->sum('extra_duty_amount'),
        ];

        $attendances = $query->orderByDesc('date')->orderBy('employee_id')->paginate(20)->withQueryString();

        $employees = AssetEmployee::where('status', 'active')->orderBy('name')->get();
        $customers = Customer::orderBy('name')->get(['id', 'name', 'company_name']);

        return view('admin.attendance.index', compact('attendances', 'summary', 'employees', 'customers', 'date', 'fromDate', 'toDate'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAttendance($request);
        $data['created_by'] = auth()->id();
        $data['regular_hours'] = !empty($data['regular_hours']) ? (float) $data['regular_hours'] : 8.00;

        // Calculate Extra Duty Amount & Ensure non-null database values
        if (!empty($data['has_extra_duty'])) {
            $data['has_extra_duty']    = true;
            $data['extra_duty_hours']  = !empty($data['extra_duty_hours']) ? (float) $data['extra_duty_hours'] : 0.00;
            $data['extra_duty_rate']   = !empty($data['extra_duty_rate']) ? (float) $data['extra_duty_rate'] : 0.00;
            $data['extra_duty_amount'] = round($data['extra_duty_hours'] * $data['extra_duty_rate'], 2);
            $data['extra_duty_job']    = !empty($data['extra_duty_job']) ? trim($data['extra_duty_job']) : null;
        } else {
            $data['has_extra_duty']    = false;
            $data['extra_duty_hours']  = 0.00;
            $data['extra_duty_rate']   = 0.00;
            $data['extra_duty_amount'] = 0.00;
            $data['extra_duty_job']    = null;
        }

        $attendance = Attendance::create($data);
        $empName = $attendance->employee?->name ?? 'Staff';

        ActivityLog::log('CREATE', 'Attendance', "Recorded attendance for {$empName} on {$attendance->date->format('d/m/Y')} at site {$attendance->site_name}", $attendance);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Attendance recorded for {$empName} successfully!",
                'attendance' => $attendance,
            ]);
        }

        return redirect()->back()->with('success', "Attendance recorded for {$empName} successfully!");
    }

    public function update(Request $request, Attendance $attendance)
    {
        $data = $this->validateAttendance($request, $attendance->id);
        $data['regular_hours'] = !empty($data['regular_hours']) ? (float) $data['regular_hours'] : 8.00;

        // Calculate Extra Duty Amount & Ensure non-null database values
        if (!empty($data['has_extra_duty'])) {
            $data['has_extra_duty']    = true;
            $data['extra_duty_hours']  = !empty($data['extra_duty_hours']) ? (float) $data['extra_duty_hours'] : 0.00;
            $data['extra_duty_rate']   = !empty($data['extra_duty_rate']) ? (float) $data['extra_duty_rate'] : 0.00;
            $data['extra_duty_amount'] = round($data['extra_duty_hours'] * $data['extra_duty_rate'], 2);
            $data['extra_duty_job']    = !empty($data['extra_duty_job']) ? trim($data['extra_duty_job']) : null;
        } else {
            $data['has_extra_duty']    = false;
            $data['extra_duty_hours']  = 0.00;
            $data['extra_duty_rate']   = 0.00;
            $data['extra_duty_amount'] = 0.00;
            $data['extra_duty_job']    = null;
        }

        $attendance->update($data);
        $empName = $attendance->employee?->name ?? 'Staff';

        ActivityLog::log('UPDATE', 'Attendance', "Updated attendance for {$empName} on {$attendance->date->format('d/m/Y')}", $attendance);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Attendance updated successfully!",
                'attendance' => $attendance,
            ]);
        }

        return redirect()->back()->with('success', "Attendance updated successfully!");
    }

    public function destroy(Attendance $attendance)
    {
        $empName = $attendance->employee?->name ?? 'Staff';
        $dateStr = $attendance->date?->format('d/m/Y');
        $attendance->delete();

        ActivityLog::log('DELETE', 'Attendance', "Deleted attendance for {$empName} on {$dateStr}", $attendance);

        return redirect()->back()->with('success', "Attendance record deleted successfully.");
    }

    public function getPeriodData(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:asset_employees,id',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
        ]);

        $employee = AssetEmployee::findOrFail($request->employee_id);
        $attendances = Attendance::with('customer')
            ->where('employee_id', $employee->id)
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->orderBy('date')
            ->get();

        $daysPresent = $attendances->where('status', 'present')->count();
        $daysHalfDay = $attendances->where('status', 'half_day')->count();
        $daysAbsent = $attendances->where('status', 'absent')->count();
        $totalWorkingDays = $daysPresent + ($daysHalfDay * 0.5);

        $totalExtraDutyHours = $attendances->sum('extra_duty_hours');
        $totalExtraDutyAmount = $attendances->sum('extra_duty_amount');

        // Base wage calculation based on salary type
        $baseSalaryRate = (float) $employee->base_salary;
        $baseSalaryEarned = 0;

        if ($employee->salary_type === 'weekly') {
            $dailyRate = $baseSalaryRate / 6;
            $baseSalaryEarned = round($dailyRate * $totalWorkingDays, 2);
        } elseif ($employee->salary_type === 'monthly') {
            $dailyRate = $baseSalaryRate / 30;
            $baseSalaryEarned = round($dailyRate * $totalWorkingDays, 2);
        } elseif (in_array($employee->salary_type, ['40_days', '60_days', '70_days', 'custom_days'])) {
            $cycleDays = max(1, (int) ($employee->settlement_days ?: 40));
            $dailyRate = $baseSalaryRate / $cycleDays;
            $baseSalaryEarned = round($dailyRate * $totalWorkingDays, 2);
        } else {
            // Daily / on demand: base_salary is the per-day rate
            $baseSalaryEarned = round($baseSalaryRate * $totalWorkingDays, 2);
        }

        $gross = $baseSalaryEarned + $totalExtraDutyAmount;

        return response()->json([
            'employee' => [
                'id'                  => $employee->id,
                'name'                => $employee->name,
                'employee_code'       => $employee->employee_code,
                'salary_type'         => $employee->salary_type,
                'salary_type_label'   => $employee->salary_type_label,
                'settlement_days'     => (int) ($employee->settlement_days ?: 7),
                'base_salary'         => $baseSalaryRate,
                'extra_duty_rate'     => (float) $employee->extra_duty_rate,
                'daily_working_hours' => (float) ($employee->daily_working_hours ?: 8),
            ],
            'summary' => [
                'days_present'            => $daysPresent,
                'days_half_day'           => $daysHalfDay,
                'days_absent'             => $daysAbsent,
                'total_working_days'      => $totalWorkingDays,
                'base_salary_rate'        => $baseSalaryRate,
                'base_salary_earned'      => $baseSalaryEarned,
                'total_extra_duty_hours'  => $totalExtraDutyHours,
                'total_extra_duty_amount' => $totalExtraDutyAmount,
                'gross_amount'            => $gross,
                'net_amount'              => $gross,
            ],
            'attendances' => $attendances->map(function ($att) {
                return [
                    'id'                => $att->id,
                    'date'              => $att->date->format('Y-m-d'),
                    'date_formatted'    => $att->date->format('d/m/Y (D)'),
                    'site_name'         => $att->site_name ?? 'Regular Site',
                    'punch_in'          => $att->punch_in ? Carbon::parse($att->punch_in)->format('h:i A') : '-',
                    'punch_out'         => $att->punch_out ? Carbon::parse($att->punch_out)->format('h:i A') : '-',
                    'status'            => $att->status,
                    'status_badge'      => $att->status_badge,
                    'regular_hours'     => (float) $att->regular_hours,
                    'has_extra_duty'    => (bool) $att->has_extra_duty,
                    'extra_duty_hours'  => (float) $att->extra_duty_hours,
                    'extra_duty_rate'   => (float) $att->extra_duty_rate,
                    'extra_duty_job'    => $att->extra_duty_job ?? '-',
                    'extra_duty_amount' => (float) $att->extra_duty_amount,
                    'notes'             => $att->notes ?? '',
                ];
            }),
        ]);
    }

    protected function validateAttendance(Request $request, $id = null): array
    {
        return $request->validate([
            'employee_id'       => ['required', 'exists:asset_employees,id'],
            'date'              => ['required', 'date'],
            'site_name'         => ['nullable', 'string', 'max:255'],
            'customer_id'       => ['nullable', 'exists:customers,id'],
            'punch_in'          => ['nullable', 'string', 'max:20'],
            'punch_out'         => ['nullable', 'string', 'max:20'],
            'status'            => ['required', 'in:present,half_day,absent,holiday,leave'],
            'regular_hours'     => ['nullable', 'numeric', 'min:0', 'max:24'],
            'has_extra_duty'    => ['nullable', 'boolean'],
            'extra_duty_hours'  => ['nullable', 'numeric', 'min:0', 'max:24'],
            'extra_duty_rate'   => ['nullable', 'numeric', 'min:0'],
            'extra_duty_job'    => ['nullable', 'string', 'max:255'],
            'notes'             => ['nullable', 'string'],
        ]);
    }
}
