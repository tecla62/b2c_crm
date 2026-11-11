<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    protected $fillable = [
        'user_id', 'ip_address', 'user_agent', 'browser',
        'device', 'platform', 'location', 'status',
        'failure_reason', 'login_at', 'logout_at', 'session_id',
    ];

    protected $casts = [
        'login_at'  => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'success' => '<span class="badge bg-success">Success</span>',
            'failed'  => '<span class="badge bg-danger">Failed</span>',
            'locked'  => '<span class="badge bg-warning text-dark">Locked</span>',
            default   => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
