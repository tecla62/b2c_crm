<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'employee_id', 'date', 'site_name', 'customer_id',
        'punch_in', 'punch_out', 'status', 'regular_hours',
        'has_extra_duty', 'extra_duty_hours', 'extra_duty_rate',
        'extra_duty_job', 'extra_duty_amount', 'notes', 'created_by',
    ];

    protected $casts = [
        'date'              => 'date',
        'regular_hours'     => 'float',
        'has_extra_duty'    => 'boolean',
        'extra_duty_hours'  => 'float',
        'extra_duty_rate'   => 'float',
        'extra_duty_amount' => 'float',
    ];

    public function employee()
    {
        return $this->belongsTo(AssetEmployee::class, 'employee_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'present'  => '<span class="badge bg-success">Present</span>',
            'half_day' => '<span class="badge bg-warning text-dark">Half Day</span>',
            'absent'   => '<span class="badge bg-danger">Absent</span>',
            'holiday'  => '<span class="badge bg-info text-dark">Holiday</span>',
            'leave'    => '<span class="badge bg-secondary">Leave</span>',
            default    => '<span class="badge bg-light text-dark">' . ucfirst($this->status) . '</span>',
        };
    }
}
