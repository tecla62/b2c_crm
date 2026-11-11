{{-- ── Invoice Settings ─────────────────────────────────────── --}}
<div class="settings-section-title"><i class="bi bi-receipt me-2 text-primary"></i>Invoice Settings</div>
<p class="settings-section-sub">Configure invoice numbering, currency, tax and footer notes.</p>

<form id="form-invoice" action="{{ route('admin.settings.save', 'invoice') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-hash"></i><h6>Numbering & Prefix</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-12">
                    <label class="settings-label">Invoice Prefix</label>
                    <input type="text" name="invoice_prefix" class="settings-input" value="{{ $settings['invoice_prefix'] ?? 'INV' }}" placeholder="INV">
                    <span class="settings-hint">Example: INV-2026-0001</span>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-currency-rupee"></i><h6>Currency & Tax</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="settings-label">Currency Symbol</label>
                    <input type="text" name="currency_symbol" class="settings-input" value="{{ $settings['currency_symbol'] ?? '₹' }}" placeholder="₹">
                </div>
                <div class="col-md-3">
                    <label class="settings-label">Currency Position</label>
                    <select name="currency_position" class="settings-input">
                        <option value="before" {{ ($settings['currency_position'] ?? 'before') === 'before' ? 'selected' : '' }}>Before (₹100)</option>
                        <option value="after"  {{ ($settings['currency_position'] ?? '') === 'after'  ? 'selected' : '' }}>After (100₹)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="settings-label">Default Tax %</label>
                    <input type="number" name="default_tax" class="settings-input" value="{{ $settings['default_tax'] ?? '18' }}" min="0" max="100" step="0.01">
                </div>
                <div class="col-md-3">
                    <label class="settings-label">Decimal Precision</label>
                    <select name="decimal_precision" class="settings-input">
                        @foreach([0,1,2,3] as $d)
                        <option value="{{ $d }}" {{ ($settings['decimal_precision'] ?? '2') == $d ? 'selected' : '' }}>{{ $d }} decimal{{ $d !== 1 ? 's' : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-card-text"></i><h6>Footer Notes</h6></div>
        <div class="settings-card-body">
            <label class="settings-label">Invoice Footer Notes</label>
            <textarea name="invoice_footer_notes" class="settings-input" rows="4" placeholder="Thank you for your business!">{{ $settings['invoice_footer_notes'] ?? '' }}</textarea>
            <span class="settings-hint">Appears at the bottom of every invoice.</span>
        </div>
    </div>

    <div class="settings-save-bar">
        <span class="save-status" id="save-status-invoice"></span>
        <button type="button" id="btn-save-invoice" class="btn btn-primary px-4" onclick="saveSettings('invoice')">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
    </div>
</form>
