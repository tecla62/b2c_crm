<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'payroll_number', 'employee_id', 'period_type', 'start_date', 'end_date',
        'days_present', 'days_half_day', 'days_absent', 'total_working_days',
        'base_salary_rate', 'base_salary_earned',
        'total_extra_duty_hours', 'total_extra_duty_amount',
        'allowance_amount', 'allowance_notes',
        'deduction_amount', 'deduction_notes',
        'gross_amount', 'net_amount',
        'status', 'payment_method', 'payment_date', 'payment_reference',
        'notes', 'created_by',
    ];

    protected $casts = [
        'start_date'               => 'date',
        'end_date'                 => 'date',
        'payment_date'             => 'date',
        'days_present'             => 'float',
        'days_half_day'            => 'float',
        'days_absent'              => 'float',
        'total_working_days'       => 'float',
        'base_salary_rate'         => 'float',
        'base_salary_earned'       => 'float',
        'total_extra_duty_hours'   => 'float',
        'total_extra_duty_amount'  => 'float',
        'allowance_amount'         => 'float',
        'deduction_amount'         => 'float',
        'gross_amount'             => 'float',
        'net_amount'               => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(AssetEmployee::class, 'employee_id');
    }

    public function items()
    {
        return $this->hasMany(PayrollItem::class, 'payroll_id')->orderBy('date');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generateNumber(): string
    {
        $prefix = 'PAY-' . date('Y') . '-';
        $last = static::withTrashed()->where('payroll_number', 'like', $prefix . '%')
                    ->orderByDesc('id')->value('payroll_number');
        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'approved' => '<span class="badge bg-primary">Approved</span>',
            'paid'     => '<span class="badge bg-success">Paid</span>',
            default    => '<span class="badge bg-warning text-dark">Draft</span>',
        };
    }
}
