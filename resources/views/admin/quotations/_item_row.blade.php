@php
$idx  = $index ?? '__IDX__';
$mode = $item?->area_mode ?? 'none';
@endphp
<div class="item-row">

    <input type="hidden" name="items[{{ $idx }}][service_id]"  value="{{ $item?->service_id }}">
    <input type="hidden" name="items[{{ $idx }}][category_id]" value="{{ $item?->category_id }}">
    <input type="hidden" name="items[{{ $idx }}][area_mode]"   class="area-mode-input" value="{{ $mode }}">

    {{-- Header --}}
    <div class="item-header">
        <input type="checkbox" class="item-select-cb form-check-input mt-0"
               style="width:17px;height:17px;flex-shrink:0;" title="Select for bulk"
               onchange="onSelectCb()">
        <span class="item-num">{{ is_numeric($idx) ? $idx + 1 : 1 }}</span>
        <span class="item-title-text fw-600" style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:13px;">
            {{ $item?->title ?: 'New Item' }}
        </span>
        <button type="button" class="btn-remove-item" onclick="removeItem(this)" title="Remove">
            <i class="bi bi-x"></i>
        </button>
    </div>

    {{-- Title --}}
    <div class="mb-2">
        <input type="text" name="items[{{ $idx }}][title]"
               class="form-control form-control-sm"
               placeholder="Item / Service title *"
               value="{{ $item?->title }}" required>
    </div>

    {{-- Description --}}
    <div class="mb-2">
        <textarea name="items[{{ $idx }}][description]"
                  class="form-control form-control-sm" rows="2"
                  placeholder="Description (optional)">{{ $item?->description }}</textarea>
    </div>

    {{-- Mode tabs --}}
    <div class="mode-tabs mb-2">
        <button type="button" class="mode-tab {{ $mode==='none'     ? 'active':'' }}" onclick="setMode(this,'none')">
            <i class="bi bi-currency-rupee"></i><span class="mode-label"> Amount</span>
        </button>
        <button type="button" class="mode-tab {{ $mode==='sqft'     ? 'active':'' }}" onclick="setMode(this,'sqft')">
            <i class="bi bi-aspect-ratio"></i><span class="mode-label"> Sq.Ft</span>
        </button>
        <button type="button" class="mode-tab {{ $mode==='nos'      ? 'active':'' }}" onclick="setMode(this,'nos')">
            <i class="bi bi-123"></i><span class="mode-label"> Nos</span>
        </button>
        <button type="button" class="mode-tab {{ $mode==='nos_sqft' ? 'active':'' }}" onclick="setMode(this,'nos_sqft')">
            <i class="bi bi-grid-1x2"></i><span class="mode-label"> Nos+Sqft</span>
        </button>
    </div>

    {{-- Sq.Ft panel --}}
    <div class="mode-panel {{ $mode==='sqft' ? '' : 'd-none' }}" data-panel="sqft">
        <div class="row g-2 mb-1">
            <div class="col-6 col-sm-4">
                <label class="field-label">Length (ft)</label>
                <input type="number" name="items[{{ $idx }}][area_length]"
                       class="form-control form-control-sm" step="0.01" min="0" placeholder="0.00"
                       value="{{ $item?->area_length }}" oninput="calcArea(this.closest('.item-row'))">
            </div>
            <div class="col-6 col-sm-4">
                <label class="field-label">Width (ft)</label>
                <input type="number" name="items[{{ $idx }}][area_width]"
                       class="form-control form-control-sm" step="0.01" min="0" placeholder="0.00"
                       value="{{ $item?->area_width }}" oninput="calcArea(this.closest('.item-row'))">
            </div>
            <div class="col-12 col-sm-4">
                <label class="field-label">Rate/sqft (₹)</label>
                <input type="number" name="items[{{ $idx }}][rate_per_sft]"
                       class="form-control form-control-sm" step="0.01" min="0" placeholder="0.00"
                       value="{{ $item?->rate_per_sft }}" oninput="calcArea(this.closest('.item-row'))">
            </div>
        </div>
        <div class="area-result-row">
            Area: <strong class="area-computed">
                @if($item?->area_mode==='sqft')
                    {{ number_format(($item->area_length??0)*($item->area_width??0),2) }} sqft
                @endif
            </strong>
        </div>
    </div>

    {{-- Nos only panel --}}
    <div class="mode-panel {{ $mode==='nos' ? '' : 'd-none' }}" data-panel="nos">
        <div class="row g-2 mb-1">
            <div class="col-6">
                <label class="field-label">Nos</label>
                <input type="number" name="items[{{ $idx }}][area_nos]"
                       class="form-control form-control-sm nos-input" step="1" min="0" placeholder="0"
                       value="{{ $item?->area_nos }}" oninput="calcArea(this.closest('.item-row'))">
            </div>
            <div class="col-6">
                <label class="field-label">Rate/Nos (₹)</label>
                <input type="number" name="items[{{ $idx }}][rate_per_nos]"
                       class="form-control form-control-sm" step="0.01" min="0" placeholder="0.00"
                       value="{{ $item?->rate_per_nos }}" oninput="calcArea(this.closest('.item-row'))">
            </div>
        </div>
        <div class="area-result-row">
            Nos: <strong class="area-computed">{{ $item?->area_mode==='nos' ? ($item->area_nos??0) : '' }}</strong>
        </div>
    </div>

    {{-- Nos + Sqft panel --}}
    <div class="mode-panel {{ $mode==='nos_sqft' ? '' : 'd-none' }}" data-panel="nos_sqft">
        <div class="row g-2 mb-1">
            <div class="col-6 col-sm-3">
                <label class="field-label">Nos</label>
                <input type="number" name="items[{{ $idx }}][area_nos]"
                       class="form-control form-control-sm nos-input" step="1" min="0" placeholder="0"
                       value="{{ $item?->area_nos }}" oninput="calcArea(this.closest('.item-row'))">
            </div>
            <div class="col-6 col-sm-3">
                <label class="field-label">Size (sqft)</label>
                <input type="number" name="items[{{ $idx }}][area_size]"
                       class="form-control form-control-sm" step="0.01" min="0" placeholder="0.00"
                       value="{{ $item?->area_size }}" oninput="calcArea(this.closest('.item-row'))">
            </div>
            <div class="col-12 col-sm-6">
                <label class="field-label">Rate/sqft (₹)</label>
                <input type="number" name="items[{{ $idx }}][rate_per_sft]"
                       class="form-control form-control-sm" step="0.01" min="0" placeholder="0.00"
                       value="{{ $item?->rate_per_sft }}" oninput="calcArea(this.closest('.item-row'))">
            </div>
        </div>
        <div class="area-result-row">
            Area: <strong class="area-computed">
                @if($item?->area_mode==='nos_sqft')
                    {{ number_format(($item->area_nos??0)*($item->area_size??0),2) }} sqft
                @endif
            </strong>
        </div>
    </div>

    {{-- Amount / Qty / Total --}}
    <div class="row g-2 mt-1 align-items-end">
        <div class="col-12 col-sm-5">
            <label class="field-label">Amount (₹)</label>
            <input type="number" name="items[{{ $idx }}][price]"
                   class="form-control form-control-sm price-field"
                   step="0.01" min="0" placeholder="0.00"
                   value="{{ $item ? number_format($item->price,2,'.','') : '' }}"
                   oninput="recalc()">
        </div>
        <div class="col-6 col-sm-3">
            <label class="field-label">Qty</label>
            <input type="number" name="items[{{ $idx }}][qty]"
                   class="form-control form-control-sm qty-field"
                   step="1" min="1" placeholder="1"
                   value="{{ $item ? $item->qty : 1 }}"
                   oninput="recalc()">
        </div>
        <div class="col-6 col-sm-4">
            <label class="field-label">Total</label>
            <div class="line-total-box item-line-total">
                {{ $item ? format_currency($item->price * $item->qty) : format_currency(0) }}
            </div>
        </div>
    </div>

</div>
