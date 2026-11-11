<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'asset_code', 'name', 'category', 'unit', 'location',
        'total_quantity', 'issued_quantity', 'damaged_quantity',
        'maintenance_quantity', 'lost_quantity', 'minimum_quantity',
        'condition', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'total_quantity' => 'integer',
        'issued_quantity' => 'integer',
        'damaged_quantity' => 'integer',
        'maintenance_quantity' => 'integer',
        'lost_quantity' => 'integer',
        'minimum_quantity' => 'integer',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function movements()
    {
        return $this->hasMany(AssetMovement::class)->latest('movement_date')->latest();
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class)->latest('issue_date')->latest();
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->total_quantity - $this->issued_quantity - $this->damaged_quantity - $this->maintenance_quantity - $this->lost_quantity);
    }

    public function getStockBadgeAttribute(): string
    {
        if ($this->status !== 'active') {
            return '<span class="badge bg-secondary">Inactive</span>';
        }

        if ($this->available_quantity <= 0) {
            return '<span class="badge bg-danger">Not Available</span>';
        }

        if ($this->available_quantity <= $this->minimum_quantity) {
            return '<span class="badge bg-warning text-dark">Low Stock</span>';
        }

        return '<span class="badge bg-success">Available</span>';
    }

    public function getConditionBadgeAttribute(): string
    {
        return match ($this->condition) {
            'new' => '<span class="badge" style="background:#dcfce7;color:#16a34a;">New</span>',
            'good' => '<span class="badge" style="background:#dbeafe;color:#1d4ed8;">Good</span>',
            'repair' => '<span class="badge" style="background:#fef3c7;color:#d97706;">Repair</span>',
            'damaged' => '<span class="badge" style="background:#fee2e2;color:#dc2626;">Damaged</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
