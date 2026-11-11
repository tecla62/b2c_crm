{{-- ── PDF & Document Settings ──────────────────────────────── --}}
<div class="settings-section-title"><i class="bi bi-file-earmark-pdf me-2 text-primary"></i>PDF & Document Settings</div>
<p class="settings-section-sub">Configure logos, watermark, signature and footer for quotations and invoices.</p>

<form id="form-pdf" action="{{ route('admin.settings.save', 'pdf') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- ── Document Logos ── --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-image"></i><h6>Document Logos</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="settings-label">Quotation / Estimation Logo</label>
                    <p class="settings-hint mb-2"><i class="bi bi-info-circle"></i> Used on quotation print. Falls back to Company Logo if not set.</p>
                    <div class="img-upload-wrap">
                        <input type="file" name="pdf_estimation_logo" class="img-file-input"
                               data-preview="prev-pdf_estimation_logo" accept="image/*">
                        <div class="upload-icon"><i class="bi bi-cloud-upload"></i></div>
                        <div class="upload-text">Click or drag to upload</div>
                        <div class="upload-hint">PNG, JPG — max 2MB</div>
                    </div>
                    <div class="img-preview-wrap" id="prev-pdf_estimation_logo"
                         style="{{ ($settings['pdf_estimation_logo'] ?? '') ? '' : 'display:none' }}">
                        <img src="{{ setting_image('pdf_estimation_logo') }}" alt="Estimation Logo">
                        <button type="button" class="btn-remove-img"
                                data-key="pdf_estimation_logo" data-preview="prev-pdf_estimation_logo">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Invoice Logo</label>
                    <p class="settings-hint mb-2"><i class="bi bi-info-circle"></i> Used on invoice print. Falls back to Company Logo if not set.</p>
                    <div class="img-upload-wrap">
                        <input type="file" name="pdf_invoice_logo" class="img-file-input"
                               data-preview="prev-pdf_invoice_logo" accept="image/*">
                        <div class="upload-icon"><i class="bi bi-cloud-upload"></i></div>
                        <div class="upload-text">Click or drag to upload</div>
                        <div class="upload-hint">PNG, JPG — max 2MB</div>
                    </div>
                    <div class="img-preview-wrap" id="prev-pdf_invoice_logo"
                         style="{{ ($settings['pdf_invoice_logo'] ?? '') ? '' : 'display:none' }}">
                        <img src="{{ setting_image('pdf_invoice_logo') }}" alt="Invoice Logo">
                        <button type="button" class="btn-remove-img"
                                data-key="pdf_invoice_logo" data-preview="prev-pdf_invoice_logo">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Watermark ── --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-droplet-half"></i><h6>Watermark</h6></div>
        <div class="settings-card-body">
            <p style="margin-bottom: 20px; font-size: 12px; color: #64748b; line-height: 1.6;">
                <i class="bi bi-info-circle" style="color: {{ $settings['pdf_header_color'] ?? '#4f46e5' }};"></i>
                Add a professional watermark to your documents. Upload a PNG image with transparent background and adjust the opacity to your preference. The watermark will appear diagonally centered on the PDF.
            </p>
            <div class="row g-4 align-items-start">

                {{-- Upload --}}
                <div class="col-md-5">
                    <label class="settings-label">Watermark Image</label>
                    <p class="settings-hint mb-2"><i class="bi bi-info-circle"></i> Recommended: 300x300px transparent PNG. Examples: company logo, "DRAFT", "APPROVED", etc.</p>
                    <div class="img-upload-wrap">
                        <input type="file" name="pdf_watermark_image" id="wm-file-input"
                               class="img-file-input" data-preview="prev-pdf_watermark_image" accept="image/*">
                        <div class="upload-icon"><i class="bi bi-droplet-half"></i></div>
                        <div class="upload-text">Click or drag to upload</div>
                        <div class="upload-hint">PNG with transparency recommended</div>
                    </div>
                    <div class="img-preview-wrap" id="prev-pdf_watermark_image"
                         style="{{ ($settings['pdf_watermark_image'] ?? '') ? '' : 'display:none' }}">
                        <img src="{{ setting_image('pdf_watermark_image') }}" alt="Watermark">
                        <button type="button" class="btn-remove-img"
                                data-key="pdf_watermark_image" data-preview="prev-pdf_watermark_image"
                                onclick="clearWatermarkPreview()">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>

                    <div class="mt-3">
                        <label class="settings-label">Opacity: <span id="opacityVal">{{ $settings['pdf_watermark_opacity'] ?? '15' }}%</span></label>
                        <input type="range" name="pdf_watermark_opacity" id="wm-opacity"
                               class="form-range" min="5" max="60" step="1"
                               value="{{ $settings['pdf_watermark_opacity'] ?? '15' }}"
                               oninput="updateWatermarkPreview()">
                        <div class="d-flex justify-content-between" style="font-size:11px;color:#94a3b8;">
                            <span>Subtle (5%)</span><span>Strong (60%)</span>
                        </div>
                    </div>
                </div>

                {{-- Live Preview --}}
                <div class="col-md-7">
                    <label class="settings-label">Live Preview</label>
                    <div id="wm-preview-box" style="
                        position:relative; overflow:hidden;
                        background:#fff; border:1.5px solid #e2e8f0; border-radius:12px;
                        height:260px; display:flex; align-items:center; justify-content:center;
                        box-shadow:0 2px 12px rgba(0,0,0,.06);">

                        {{-- Fake document lines --}}
                        <div style="position:absolute;inset:0;padding:20px;">
                            @foreach([60,20,20,20,20,20,20,20,20] as $w)
                            <div style="height:8px;background:#f1f5f9;border-radius:4px;margin-bottom:8px;width:{{ $w }}%;"></div>
                            @endforeach
                        </div>

                        {{-- Watermark overlay --}}
                        <img id="wm-preview-img"
                             src="{{ setting_image('pdf_watermark_image') ?: '' }}"
                             style="
                                position:absolute; top:50%; left:50%;
                                transform:translate(-50%,-50%) rotate(-30deg);
                                width:200px; pointer-events:none;
                                opacity:{{ ($settings['pdf_watermark_opacity'] ?? 15) / 100 }};
                                {{ ($settings['pdf_watermark_image'] ?? '') ? '' : 'display:none' }}">

                        @if(!($settings['pdf_watermark_image'] ?? ''))
                        <div id="wm-empty-msg" style="position:relative;z-index:2;text-align:center;color:#94a3b8;">
                            <i class="bi bi-droplet-half" style="font-size:28px;display:block;margin-bottom:6px;"></i>
                            <span style="font-size:12px;">Upload a watermark image to preview</span>
                        </div>
                        @else
                        <div id="wm-empty-msg" style="display:none;position:relative;z-index:2;text-align:center;color:#94a3b8;">
                            <i class="bi bi-droplet-half" style="font-size:28px;display:block;margin-bottom:6px;"></i>
                            <span style="font-size:12px;">Upload a watermark image to preview</span>
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── Signature & Seal ── --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-pen"></i><h6>Professional Signature & Company Seal</h6></div>
        <div class="settings-card-body">
            <p style="margin-bottom: 20px; font-size: 12px; color: #64748b; line-height: 1.6;">
                <i class="bi bi-info-circle" style="color: {{ $settings['pdf_header_color'] ?? '#4f46e5' }};"></i>
                Upload your authorized signature and official company seal images. These will be displayed prominently in the authorization section of quotations. 
                For a professional appearance, use high-quality PNG images (300 DPI) with transparent backgrounds.
            </p>

            {{-- Professional Guidelines Box --}}
            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 24px;">
                <div style="font-size: 11px; font-weight: 700; color: #1e293b; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.5px;">
                    📸 Professional Image Guidelines
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 11px; color: #475569;">
                    <div>
                        <strong style="display: block; margin-bottom: 6px; color: #1e293b;">Authorized Signature:</strong>
                        • Scan at 300 DPI<br>
                        • Height: 80-120px<br>
                        • Use PNG format<br>
                        • Transparent background
                    </div>
                    <div>
                        <strong style="display: block; margin-bottom: 6px; color: #1e293b;">Company Seal:</strong>
                        • Scan at 300 DPI<br>
                        • Size: 150x150px - 250x250px<br>
                        • Use PNG format<br>
                        • Transparent background
                    </div>
                </div>
            </div>

            <div class="row g-4">
                @foreach(['pdf_signature' => 'Authorized Signature', 'pdf_company_seal' => 'Company Seal / Stamp'] as $key => $label)
                <div class="col-md-6">
                    <label class="settings-label">{{ $label }}</label>
                    <p class="settings-hint mb-2"><i class="bi bi-info-circle"></i> 
                        @if(strpos($key, 'signature') !== false)
                            High-resolution authorized signature (scan of actual signature). Professional and secure.
                        @else
                            Official company seal, stamp, or emblem. Adds credibility and authenticity to documents.
                        @endif
                    </p>
                    <div class="img-upload-wrap">
                        <input type="file" name="{{ $key }}" class="img-file-input"
                               data-preview="prev-{{ $key }}" accept="image/*">
                        <div class="upload-icon"><i class="bi bi-pen"></i></div>
                        <div class="upload-text">Click or drag to upload</div>
                        <div class="upload-hint">PNG with transparent background</div>
                    </div>
                    <div class="img-preview-wrap" id="prev-{{ $key }}"
                         style="{{ ($settings[$key] ?? '') ? '' : 'display:none' }}">
                        <img src="{{ setting_image($key) }}" alt="{{ $label }}">
                        <button type="button" class="btn-remove-img"
                                data-key="{{ $key }}" data-preview="prev-{{ $key }}">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ── Colors & Footer ── --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-palette"></i><h6>Colors & Footer</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="settings-label">PDF Header Color</label>
                    <p class="settings-hint mb-2"><i class="bi bi-info-circle"></i> Used for table headers and title color in quotation print.</p>
                    <div class="color-picker-wrap">
                        <input type="color" name="pdf_header_color" value="{{ $settings['pdf_header_color'] ?? '#4f46e5' }}">
                        <input type="text" class="color-hex" value="{{ $settings['pdf_header_color'] ?? '#4f46e5' }}" maxlength="7">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="settings-label">PDF Footer Text</label>
                    <p class="settings-hint mb-2"><i class="bi bi-info-circle"></i> Professional footer displayed at the bottom of every PDF. Include legal disclaimers, terms notices, etc.</p>
                    <textarea name="pdf_footer_text" class="settings-input" rows="3"
                              placeholder="This is a computer generated document. No signature is required.">{{ $settings['pdf_footer_text'] ?? 'This is a computer generated document.' }}</textarea>
                    <div style="font-size: 11px; color: #94a3b8; margin-top: 6px;">
                        ℹ️ Keep text concise. Avoid special characters.
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- ── Default Invoice Terms ── --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-receipt"></i><h6>Default Invoice Terms</h6></div>
        <div class="settings-card-body">
            <p style="margin-bottom: 20px; font-size: 12px; color: #64748b; line-height: 1.6;">
                <i class="bi bi-info-circle" style="color: {{ $settings['pdf_header_color'] ?? '#4f46e5' }};"></i>
                Set default Terms, Guarantee, and Payment terms that will be automatically used in invoices if not explicitly set. These defaults can be overridden per invoice.
            </p>
            <div class="row g-4">
                <div class="col-md-12">
                    <label class="settings-label"><i class="bi bi-file-earmark-text"></i> Default Terms &amp; Conditions</label>
                    <p class="settings-hint mb-2"><i class="bi bi-info-circle"></i> Default terms that apply to all invoices unless overridden.</p>
                    <textarea name="invoice_terms" class="settings-input" rows="4"
                              placeholder="e.g., 1. All prices are exclusive of taxes.&#10;2. Payment is due within 15 days.">{{ $settings['invoice_terms'] ?? '' }}</textarea>
                </div>
            </div>
            <div class="row g-4 mt-3">
                <div class="col-md-6">
                    <label class="settings-label"><i class="bi bi-shield-check"></i> Default Guarantee &amp; Warranty</label>
                    <p class="settings-hint mb-2"><i class="bi bi-info-circle"></i> Default guarantee/warranty terms for all invoices.</p>
                    <textarea name="invoice_guarantee" class="settings-input" rows="4"
                              placeholder="e.g., 1 Year warranty on workmanship.&#10;Defects will be rectified at no additional cost.">{{ $settings['invoice_guarantee'] ?? '' }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="settings-label"><i class="bi bi-credit-card"></i> Default Payment Terms</label>
                    <p class="settings-hint mb-2"><i class="bi bi-info-circle"></i> Default payment instructions for all invoices.</p>
                    <textarea name="invoice_payment" class="settings-input" rows="4"
                              placeholder="e.g., Please make payment via bank transfer to account 123456789.">{{ $settings['invoice_payment'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Save --}}
    <div class="settings-save-bar">
        <span class="save-status" id="save-status-pdf"></span>
        <button type="button" id="btn-save-pdf" class="btn btn-primary px-4" onclick="saveSettings('pdf')">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
    </div>
</form>

@push('scripts')
<script>
// Watermark opacity slider live update
function updateWatermarkPreview() {
    const val = $('#wm-opacity').val();
    $('#opacityVal').text(val + '%');
    $('#wm-preview-img').css('opacity', val / 100);
}

// When a new watermark file is selected — show in preview box
$(document).on('change', '#wm-file-input', function() {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = e => {
        $('#wm-preview-img').attr('src', e.target.result).show();
        $('#wm-empty-msg').hide();
        updateWatermarkPreview();
    };
    reader.readAsDataURL(file);
});

function clearWatermarkPreview() {
    $('#wm-preview-img').attr('src','').hide();
    $('#wm-empty-msg').show();
}
</script>
@endpush
