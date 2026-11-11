<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'email', 'mobile', 'phone', 'avatar',
        'company_name', 'gst_number', 'website',
        'address', 'city', 'state', 'country', 'pincode',
        'source', 'type', 'status', 'notes',
        'assigned_to', 'created_by',
    ];

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class)->latest('transaction_date');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class)->latest('expense_date');
    }

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar && file_exists(public_path('storage/' . $this->avatar))) {
            return asset('storage/' . $this->avatar);
        }
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=0ea5e9&color=fff&size=128";
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'   => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>',
            'blocked'  => '<span class="badge bg-danger">Blocked</span>',
            default    => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'company'    => '<span class="badge bg-primary">Company</span>',
            'individual' => '<span class="badge bg-info">Individual</span>',
            default      => '<span class="badge bg-secondary">' . ucfirst($this->type) . '</span>',
        };
    }
}
