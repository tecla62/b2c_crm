<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    protected $fillable = [
        'payroll_id', 'attendance_id', 'date', 'site_name',
        'punch_in', 'punch_out', 'status', 'regular_hours',
        'extra_duty_hours', 'extra_duty_rate', 'extra_duty_job',
        'extra_duty_amount', 'notes',
    ];

    protected $casts = [
        'date'              => 'date',
        'regular_hours'     => 'float',
        'extra_duty_hours'  => 'float',
        'extra_duty_rate'   => 'float',
        'extra_duty_amount' => 'float',
    ];

    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }
}
