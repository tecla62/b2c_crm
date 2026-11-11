<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    protected $fillable = [
        'quotation_id','service_id','category_id','title','description',
        'price','qty','total','sort_order','sl_no','option_no',
        'area_mode','area_length','area_width','area_nos','area_size',
        'area_value','rate_per_sft','rate_per_nos',
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

    public function getComputedAreaAttribute(): float
    {
        return match($this->area_mode) {
            'sqft'  => ($this->area_length ?? 0) * ($this->area_width ?? 0),
            'nos'   => ($this->area_nos ?? 0) * ($this->area_size ?? 0),
            default => $this->area_value ?? 0,
        };
    }
}
