<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'mobile', 'password', 'avatar', 'role_id',
        'status', 'designation', 'department', 'address', 'city',
        'state', 'country', 'two_factor_enabled', 'two_factor_secret',
        'dark_mode', 'last_login_at', 'last_login_ip',
        'failed_login_attempts', 'locked_until',
    ];

    protected $hidden = ['password', 'remember_token', 'two_factor_secret'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at'     => 'datetime',
        'locked_until'      => 'datetime',
        'two_factor_enabled' => 'boolean',
        'dark_mode'         => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function userPermissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')->withPivot('granted');
    }

    public function loginHistories()
    {
        return $this->hasMany(LoginHistory::class)->latest();
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class)->latest();
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && (file_exists(public_path('storage/' . $this->avatar)) || file_exists(storage_path('app/public/' . $this->avatar)))) {
            return asset('storage/' . $this->avatar);
        }
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=4f46e5&color=fff&size=128";
    }

    public function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->role && $this->role->name === 'super_admin') {
            return true;
        }

        // Check user-specific override
        $userPerm = $this->userPermissions()->where('name', $permission)->first();
        if ($userPerm) {
            return (bool) $userPerm->pivot->granted;
        }

        // Check role permissions
        return $this->role ? $this->role->permissions()->where('name', $permission)->exists() : false;
    }

    public function isLocked(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getRoleNameAttribute(): string
    {
        return $this->role ? $this->role->display_name : 'No Role';
    }

    public function getRoleBadgeAttribute(): string
    {
        if (!$this->role) {
            return '<span class="badge bg-secondary text-white" style="font-weight: 600;">No Role</span>';
        }
        return $this->role->badge_html;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'    => '<span class="badge bg-success">Active</span>',
            'inactive'  => '<span class="badge bg-secondary">Inactive</span>',
            'suspended' => '<span class="badge bg-danger">Suspended</span>',
            default     => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
