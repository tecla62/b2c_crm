<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expense_number', 'invoice_id', 'transaction_id', 'customer_id',
        'expense_date', 'category', 'vendor_name', 'amount', 'payment_method',
        'reference_no', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount'       => 'float',
    ];

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function transaction() { return $this->belongsTo(Transaction::class); }
    public function customer() { return $this->belongsTo(Customer::class); }
    public function createdBy() { return $this->belongsTo(User::class, 'created_by'); }

    public static function generateNumber(): string
    {
        $prefix = 'EXP-' . date('Y') . '-';
        $last = static::withTrashed()
            ->where('expense_number', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->value('expense_number');

        $seq = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending'   => '<span class="badge" style="background:#fef3c7;color:#d97706;">Pending</span>',
            'approved'  => '<span class="badge" style="background:#dbeafe;color:#1d4ed8;">Approved</span>',
            'paid'      => '<span class="badge" style="background:#dcfce7;color:#16a34a;">Paid</span>',
            'cancelled' => '<span class="badge" style="background:#f1f5f9;color:#94a3b8;">Cancelled</span>',
            default     => '<span class="badge bg-secondary">Unknown</span>',
        };
    }
}
