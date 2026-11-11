<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quotation_number','customer_id','subject','date','valid_until','status','pricing_mode',
        'subtotal','discount','discount_percent','tax_percent','tax_amount','total',
        'terms','guarantee','payment','created_by',
    ];

    protected $casts = [
        'date'       => 'date',
        'valid_until'=> 'date',
        'subtotal'   => 'float',
        'discount'   => 'float',
        'tax_amount' => 'float',
        'total'      => 'float',
    ];

    public function customer()   { return $this->belongsTo(Customer::class); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by'); }
    public function items()      { return $this->hasMany(QuotationItem::class)->orderBy('sort_order'); }
    public function invoice()    { return $this->hasOne(Invoice::class); }

    public static function generateNumber(): string
    {
        $prefix = setting('estimation_prefix', 'QUT') . '-' . date('Y') . '-';
        $last   = static::withTrashed()->where('quotation_number','like',$prefix.'%')
                        ->orderByDesc('id')->value('quotation_number');
        $seq    = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft'    => '<span class="badge" style="background:#f1f5f9;color:#64748b;">Draft</span>',
            'sent'     => '<span class="badge" style="background:#dbeafe;color:#1d4ed8;">Sent</span>',
            'accepted' => '<span class="badge" style="background:#dcfce7;color:#16a34a;">Accepted</span>',
            'approved' => '<span class="badge" style="background:#bbf7d0;color:#15803d;">Approved</span>',
            'rejected' => '<span class="badge" style="background:#fee2e2;color:#dc2626;">Rejected</span>',
            'expired'  => '<span class="badge" style="background:#fef3c7;color:#d97706;">Expired</span>',
            default    => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
