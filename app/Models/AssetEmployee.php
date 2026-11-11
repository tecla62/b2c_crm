<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetEmployee extends Model
{
    use SoftDeletes;

    protected $table = 'asset_employees';

    protected $fillable = [
        'employee_code', 'name', 'mobile', 'designation', 'status', 'notes', 'created_by',
        'salary_type', 'settlement_days', 'base_salary', 'extra_duty_rate', 'daily_working_hours',
        'joining_date', 'address', 'bank_name', 'account_number', 'ifsc_code',
    ];

    protected $casts = [
        'settlement_days'     => 'integer',
        'base_salary'         => 'float',
        'extra_duty_rate'     => 'float',
        'daily_working_hours' => 'float',
        'joining_date'        => 'date',
    ];

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'employee_id');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class, 'employee_id');
    }

    public function latestPayroll()
    {
        return $this->hasOne(Payroll::class, 'employee_id')->latestOfMany('end_date');
    }

    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'active'
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
    }

    public function getSalaryTypeLabelAttribute(): string
    {
        return match ($this->salary_type) {
            'weekly'         => 'Weekly Wages (6 Days)',
            'monthly'        => 'Monthly Salary (30 Days)',
            '40_days'        => '40 Days Settlement',
            '60_days'        => '60 Days Settlement',
            '70_days'        => '70 Days Settlement',
            'custom_days'    => ($this->settlement_days ? "{$this->settlement_days} Days Settlement" : 'Custom Interval'),
            'daily_ondemand' => 'Daily / Flexible',
            default          => ucfirst(str_replace('_', ' ', $this->salary_type ?? 'Weekly')),
        };
    }

    public function getCycleBadgeAttribute(): string
    {
        return match ($this->salary_type) {
            'weekly'         => '<span class="badge" style="background:#e0e7ff;color:#4338ca;border:1px solid #c7d2fe;">Weekly (6D)</span>',
            'monthly'        => '<span class="badge" style="background:#fef3c7;color:#b45309;border:1px solid #fde68a;">Monthly</span>',
            '40_days'        => '<span class="badge" style="background:#e0f2fe;color:#0369a1;border:1px solid #bae6fd;">40 Days</span>',
            '60_days'        => '<span class="badge" style="background:#f3e8ff;color:#7e22ce;border:1px solid #e9d5ff;">60 Days</span>',
            '70_days'        => '<span class="badge" style="background:#fae8ff;color:#86198f;border:1px solid #f5d0fe;">70 Days</span>',
            'custom_days'    => '<span class="badge" style="background:#f1f5f9;color:#334155;border:1px solid #cbd5e1;">' . ($this->settlement_days ? "{$this->settlement_days} Days" : 'Custom') . '</span>',
            'daily_ondemand' => '<span class="badge" style="background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;">Daily / Flexible</span>',
            default          => '<span class="badge bg-light text-dark border">' . ucfirst($this->salary_type ?? 'Weekly') . '</span>',
        };
    }

    public static function generateCode(): string
    {
        $last = static::withTrashed()->where('employee_code', 'like', 'EMP-%')
                    ->orderByDesc('id')->value('employee_code');
        $num = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'EMP-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
