<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number', 'quotation_id', 'customer_id', 'subject', 
        'date', 'due_date', 'status', 'pricing_mode',
        'subtotal', 'discount', 'discount_percent', 'tax_percent', 
        'tax_amount', 'total',
        'terms', 'guarantee', 'payment', 'created_by',
    ];

    protected $casts = [
        'date'       => 'date',
        'due_date'   => 'date',
        'subtotal'   => 'float',
        'discount'   => 'float',
        'tax_amount' => 'float',
        'total'      => 'float',
    ];

    public function customer()   { return $this->belongsTo(Customer::class); }
    public function quotation()  { return $this->belongsTo(Quotation::class); }
    public function createdBy()  { return $this->belongsTo(User::class, 'created_by'); }
    public function items()      { return $this->hasMany(InvoiceItem::class)->orderBy('sort_order'); }
    public function transactions() { return $this->hasMany(Transaction::class)->latest('transaction_date'); }
    public function expenses() { return $this->hasMany(Expense::class)->latest('expense_date'); }

    public static function generateNumber(): string
    {
        $prefix = setting('invoice_prefix', 'INV') . '-' . date('Y') . '-';
        $last   = static::withTrashed()->where('invoice_number', 'like', $prefix . '%')
                        ->orderByDesc('id')->value('invoice_number');
        $seq    = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'draft'     => '<span class="badge" style="background:#f1f5f9;color:#64748b;">Draft</span>',
            'sent'      => '<span class="badge" style="background:#dbeafe;color:#1d4ed8;">Sent</span>',
            'paid'      => '<span class="badge" style="background:#dcfce7;color:#16a34a;">Paid</span>',
            'partial'   => '<span class="badge" style="background:#fef3c7;color:#d97706;">Partial</span>',
            'overdue'   => '<span class="badge" style="background:#fee2e2;color:#dc2626;">Overdue</span>',
            'cancelled' => '<span class="badge" style="background:#f1f5f9;color:#94a3b8;">Cancelled</span>',
            default     => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
