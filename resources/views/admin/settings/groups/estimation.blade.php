{{-- ── Quotation Settings ──────────────────────────────────── --}}
<div class="settings-section-title"><i class="bi bi-calculator me-2 text-primary"></i>Quotation Settings</div>
<p class="settings-section-sub">Configure quotation prefix, numbering, and default terms.</p>

<form id="form-estimation" action="{{ route('admin.settings.save', 'estimation') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-hash"></i><h6>Numbering & Prefix</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-12">
                    <label class="settings-label">Quotation Prefix</label>
                    <input type="text" name="estimation_prefix" class="settings-input" value="{{ $settings['estimation_prefix'] ?? 'QUT' }}" placeholder="QUT">
                    <span class="settings-hint">Example: QUT-2026-0001</span>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-toggles"></i><h6>Options</h6></div>
        <div class="settings-card-body">
            <div class="toggle-wrap">
                <div class="toggle-info">
                    <div class="toggle-label">Show GST Option</div>
                    <div class="toggle-desc">Display GST field on quotation forms</div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="estimation_show_gst" value="1" {{ ($settings['estimation_show_gst'] ?? '1') == '1' ? 'checked' : '' }}>
                </div>
            </div>
        </div>
    </div>


    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-patch-check"></i><h6>Quotation Defaults</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-12">
                    <label class="settings-label">Guarantee</label>
                    <textarea name="quotation_guarantee" class="settings-input" rows="3" placeholder="e.g. 1 year workmanship guarantee…">{{ $settings['quotation_guarantee'] ?? '' }}</textarea>
                    <span class="settings-hint">Auto-filled in the Guarantee field of every new quotation.</span>
                </div>
                <div class="col-12">
                    <label class="settings-label">Terms & Conditions</label>
                    <textarea name="quotation_terms" class="settings-input" rows="5" placeholder="e.g. All disputes subject to local jurisdiction…">{{ $settings['quotation_terms'] ?? '' }}</textarea>
                    <span class="settings-hint">Auto-filled in the Terms & Conditions field of every new quotation.</span>
                </div>
                <div class="col-12">
                    <label class="settings-label">Payment Terms</label>
                    <textarea name="quotation_payment" class="settings-input" rows="3" placeholder="e.g. 50% advance, balance on completion…">{{ $settings['quotation_payment'] ?? '' }}</textarea>
                    <span class="settings-hint">Auto-filled in the Payment field of every new quotation.</span>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-save-bar">
        <span class="save-status" id="save-status-estimation"></span>
        <button type="button" id="btn-save-estimation" class="btn btn-primary px-4" onclick="saveSettings('estimation')">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
    </div>
</form>
