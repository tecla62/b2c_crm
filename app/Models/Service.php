<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'title', 'sub_title', 'price', 'description', 'status', 'created_by',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    protected $casts = [
        'price' => 'float',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFormattedPriceAttribute(): string
    {
        return format_currency($this->price);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'active'   => '<span class="badge bg-success">Active</span>',
            'inactive' => '<span class="badge bg-secondary">Inactive</span>',
            default    => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    // Used by quotation/invoice line items — returns array ready to use
    public function toLineItem(): array
    {
        return [
            'service_id'  => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'price'       => $this->price,
        ];
    }
}
