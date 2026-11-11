<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AssetEmployee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = AssetEmployee::with(['latestPayroll'])
            ->withCount(['attendances', 'payrolls', 'assignments']);

        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('employee_code', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        if ($salaryType = $request->salary_type) {
            if ($salaryType === 'long_interval') {
                $query->whereIn('salary_type', ['40_days', '60_days', '70_days', 'custom_days']);
            } else {
                $query->where('salary_type', $salaryType);
            }
        }

        if ($status = $request->status) {
            $query->where('status', $status);
        }

        $summary = [
            'total'          => AssetEmployee::count(),
            'weekly'         => AssetEmployee::where('salary_type', 'weekly')->count(),
            'monthly'        => AssetEmployee::where('salary_type', 'monthly')->count(),
            'forty_days'     => AssetEmployee::where('salary_type', '40_days')->count(),
            'sixty_days'     => AssetEmployee::where('salary_type', '60_days')->count(),
            'seventy_days'   => AssetEmployee::where('salary_type', '70_days')->count(),
            'custom_days'    => AssetEmployee::where('salary_type', 'custom_days')->count(),
            'daily_ondemand' => AssetEmployee::where('salary_type', 'daily_ondemand')->count(),
            'active'         => AssetEmployee::where('status', 'active')->count(),
        ];
        $summary['long_interval'] = $summary['forty_days'] + $summary['sixty_days'] + $summary['seventy_days'] + $summary['custom_days'];

        $employees = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.employees.index', compact('employees', 'summary'));
    }

    public function create()
    {
        $employee = new AssetEmployee();
        $employee->employee_code = AssetEmployee::generateCode();
        $employee->salary_type = 'weekly';
        $employee->settlement_days = 7;
        $employee->daily_working_hours = 8;
        $employee->status = 'active';

        return view('admin.employees.form', compact('employee'));
    }

    public function store(Request $request)
    {
        $data = $this->validateEmployee($request);
        $data['created_by'] = auth()->id();

        if (empty($data['employee_code'])) {
            $data['employee_code'] = AssetEmployee::generateCode();
        }

        // Set settlement days according to chosen salary scheme
        $data['settlement_days'] = match ($data['salary_type']) {
            'weekly'         => 7,
            'monthly'        => 30,
            '40_days'        => 40,
            '60_days'        => 60,
            '70_days'        => 70,
            'daily_ondemand' => 1,
            'custom_days'    => (int) ($request->input('settlement_days') ?: 40),
            default          => (int) ($request->input('settlement_days') ?: 7),
        };

        $employee = AssetEmployee::create($data);
        ActivityLog::log('CREATE', 'Staff & HR', "Created employee {$employee->name} ({$employee->employee_code}) under {$employee->salary_type_label}", $employee);

        return redirect()->route('admin.employees.index')->with('success', "Employee {$employee->name} created successfully!");
    }

    public function edit(AssetEmployee $employee)
    {
        return view('admin.employees.form', compact('employee'));
    }

    public function update(Request $request, AssetEmployee $employee)
    {
        $data = $this->validateEmployee($request, $employee->id);

        $data['settlement_days'] = match ($data['salary_type']) {
            'weekly'         => 7,
            'monthly'        => 30,
            '40_days'        => 40,
            '60_days'        => 60,
            '70_days'        => 70,
            'daily_ondemand' => 1,
            'custom_days'    => (int) ($request->input('settlement_days') ?: ($employee->settlement_days ?: 40)),
            default          => (int) ($request->input('settlement_days') ?: 7),
        };

        $employee->update($data);

        ActivityLog::log('UPDATE', 'Staff & HR', "Updated employee {$employee->name} ({$employee->employee_code})", $employee);

        return redirect()->route('admin.employees.index')->with('success', "Employee {$employee->name} updated successfully!");
    }

    public function destroy(AssetEmployee $employee)
    {
        if ($employee->attendances()->exists() || $employee->payrolls()->exists()) {
            $employee->update(['status' => 'inactive']);
            ActivityLog::log('UPDATE', 'Staff & HR', "Deactivated employee {$employee->name} (has attendance/payroll history)", $employee);
            return redirect()->route('admin.employees.index')->with('success', "Employee has past attendance/payroll records, marked as Inactive.");
        }

        $name = $employee->name;
        $employee->delete();
        ActivityLog::log('DELETE', 'Staff & HR', "Deleted employee {$name}", $employee);

        return redirect()->route('admin.employees.index')->with('success', "Employee {$name} deleted successfully.");
    }

    public function getRate(AssetEmployee $employee)
    {
        $lastPayroll = $employee->latestPayroll;

        return response()->json([
            'id'                  => $employee->id,
            'name'                => $employee->name,
            'employee_code'       => $employee->employee_code,
            'salary_type'         => $employee->salary_type,
            'salary_type_label'   => $employee->salary_type_label,
            'settlement_days'     => (int) ($employee->settlement_days ?: 7),
            'base_salary'         => (float) $employee->base_salary,
            'extra_duty_rate'     => (float) $employee->extra_duty_rate,
            'daily_working_hours' => (float) ($employee->daily_working_hours ?: 8),
            'last_payroll_end'    => $lastPayroll ? $lastPayroll->end_date->format('Y-m-d') : null,
        ]);
    }

    protected function validateEmployee(Request $request, $id = null): array
    {
        return $request->validate([
            'name'                => ['required', 'string', 'max:150'],
            'employee_code'       => ['required', 'string', 'max:50', Rule::unique('asset_employees', 'employee_code')->ignore($id)],
            'mobile'              => ['nullable', 'string', 'max:30'],
            'designation'         => ['nullable', 'string', 'max:100'],
            'salary_type'         => ['required', 'in:weekly,monthly,40_days,60_days,70_days,custom_days,daily_ondemand'],
            'settlement_days'     => ['nullable', 'integer', 'min:1', 'max:365'],
            'base_salary'         => ['required', 'numeric', 'min:0'],
            'extra_duty_rate'     => ['nullable', 'numeric', 'min:0'],
            'daily_working_hours' => ['nullable', 'numeric', 'min:1', 'max:24'],
            'joining_date'        => ['nullable', 'date'],
            'address'             => ['nullable', 'string'],
            'bank_name'           => ['nullable', 'string', 'max:100'],
            'account_number'      => ['nullable', 'string', 'max:50'],
            'ifsc_code'           => ['nullable', 'string', 'max:30'],
            'status'              => ['required', 'in:active,inactive'],
            'notes'               => ['nullable', 'string'],
        ]);
    }
}
