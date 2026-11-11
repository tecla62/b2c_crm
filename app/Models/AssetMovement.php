<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetMovement extends Model
{
    protected $fillable = [
        'asset_item_id', 'type', 'quantity', 'movement_date',
        'asset_employee_id', 'customer_id', 'asset_assignment_id',
        'employee_name', 'site_name', 'reference_no', 'notes', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'movement_date' => 'date',
    ];

    public function assetItem()
    {
        return $this->belongsTo(AssetItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function employee()
    {
        return $this->belongsTo(AssetEmployee::class, 'asset_employee_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignment()
    {
        return $this->belongsTo(AssetAssignment::class, 'asset_assignment_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'stock_in' => 'Stock In',
            'issue' => 'Issue',
            'return' => 'Return',
            'damaged' => 'Damaged',
            'maintenance' => 'Maintenance',
            'maintenance_return' => 'Back From Maintenance',
            'lost' => 'Lost',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match ($this->type) {
            'stock_in', 'return', 'maintenance_return' => '<span class="badge bg-success">' . $this->type_label . '</span>',
            'issue' => '<span class="badge" style="background:#dbeafe;color:#1d4ed8;">Issue</span>',
            'damaged', 'lost' => '<span class="badge bg-danger">' . $this->type_label . '</span>',
            'maintenance' => '<span class="badge bg-warning text-dark">Maintenance</span>',
            default => '<span class="badge bg-secondary">' . $this->type_label . '</span>',
        };
    }
}
