<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_number', 'invoice_id', 'customer_id', 'type', 'direction',
        'transaction_date', 'amount', 'payment_method', 'reference_no', 'status',
        'notes', 'receipt_sent_at', 'receipt_sent_to', 'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount'           => 'float',
        'receipt_sent_at'  => 'datetime',
    ];

    public function invoice()   { return $this->belongsTo(Invoice::class); }
    public function customer()  { return $this->belongsTo(Customer::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }
    public function expenses()  { return $this->hasMany(Expense::class)->latest('expense_date'); }

    public static function generateNumber(): string
    {
        $prefix = 'TXN-' . date('Y') . '-';
        $last = static::withTrashed()
            ->where('transaction_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('transaction_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending'   => '<span class="badge" style="background:#fef3c7;color:#d97706;">Pending</span>',
            'cleared'   => '<span class="badge" style="background:#dcfce7;color:#16a34a;">Cleared</span>',
            'failed'    => '<span class="badge" style="background:#fee2e2;color:#dc2626;">Failed</span>',
            'cancelled' => '<span class="badge" style="background:#f1f5f9;color:#94a3b8;">Cancelled</span>',
            default     => '<span class="badge bg-secondary">Unknown</span>',
        };
    }

    public function getTypeBadgeAttribute(): string
    {
        return match($this->type) {
            'payment'    => '<span class="badge" style="background:#dbeafe;color:#1d4ed8;">Payment</span>',
            'refund'     => '<span class="badge" style="background:#ffe4e6;color:#be123c;">Refund</span>',
            'expense'    => '<span class="badge" style="background:#ffedd5;color:#c2410c;">Expense</span>',
            'adjustment' => '<span class="badge" style="background:#e0e7ff;color:#4f46e5;">Adjustment</span>',
            default      => '<span class="badge bg-secondary">Other</span>',
        };
    }
}
