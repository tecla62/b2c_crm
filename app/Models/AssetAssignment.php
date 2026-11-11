<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model
{
    protected $fillable = [
        'asset_item_id', 'assignment_group_code', 'asset_employee_id', 'customer_id',
        'quantity', 'returned_quantity', 'issue_date', 'return_date',
        'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'returned_quantity' => 'integer',
        'issue_date' => 'date',
        'return_date' => 'date',
    ];

    public function assetItem()
    {
        return $this->belongsTo(AssetItem::class);
    }

    public function employee()
    {
        return $this->belongsTo(AssetEmployee::class, 'asset_employee_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function getBalanceQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->returned_quantity);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'issued' => '<span class="badge" style="background:#dbeafe;color:#1d4ed8;">Issued</span>',
            'partial_returned' => '<span class="badge bg-warning text-dark">Partial Returned</span>',
            'returned' => '<span class="badge bg-success">Returned</span>',
            default => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
