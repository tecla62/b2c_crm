<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'display_name', 'description', 'color', 'is_system'];

    protected $casts = ['is_system' => 'boolean'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }

    public function getBadgeHtmlAttribute(): string
    {
        $color = trim((string) ($this->color ?: 'secondary'));
        $name = e($this->display_name ?: $this->name);

        if (str_starts_with($color, '#') || str_starts_with($color, 'rgb')) {
            return "<span class=\"badge text-white\" style=\"background-color: {$color} !important; border: 1px solid rgba(0,0,0,0.1); font-weight: 600;\">{$name}</span>";
        }

        $bgClass = match ($color) {
            'primary'   => 'bg-primary text-white',
            'secondary' => 'bg-secondary text-white',
            'success'   => 'bg-success text-white',
            'danger'    => 'bg-danger text-white',
            'warning'   => 'bg-warning text-dark',
            'info'      => 'bg-info text-white',
            'light'     => 'bg-light text-dark border',
            'dark'      => 'bg-dark text-white',
            default     => 'bg-secondary text-white',
        };

        return "<span class=\"badge {$bgClass}\" style=\"font-weight: 600;\">{$name}</span>";
    }
}
