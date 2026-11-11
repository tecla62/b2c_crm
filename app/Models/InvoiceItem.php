<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'service_id', 'category_id', 'title', 'description',
        'price', 'qty', 'total', 'sort_order', 'sl_no', 'option_no',
        'area_mode', 'area_length', 'area_width', 'area_nos', 'area_size',
        'area_value', 'rate_per_sft', 'rate_per_nos',
    ];

    protected $casts = [
        'price'        => 'float',
        'qty'          => 'float',
        'total'        => 'float',
        'area_length'  => 'float',
        'area_width'   => 'float',
        'area_nos'     => 'float',
        'area_size'    => 'float',
        'area_value'   => 'float',
        'rate_per_sft' => 'float',
        'rate_per_nos' => 'float',
    ];

    public function service()  { return $this->belongsTo(Service::class); }
    public function category() { return $this->belongsTo(ServiceCategory::class, 'category_id'); }
    public function invoice()  { return $this->belongsTo(Invoice::class); }
}