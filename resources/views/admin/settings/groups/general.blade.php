{{-- ── General / Company Profile ─────────────────────────────── --}}
<div class="settings-section-title"><i class="bi bi-building me-2 text-primary"></i>Company Profile</div>
<p class="settings-section-sub">Manage your company information. Changes apply instantly across all pages.</p>

{{-- ── Live Preview Card ─────────────────────────────────────── --}}
<div class="settings-card mb-3" style="border:2px dashed #e2e8f0;">
    <div class="settings-card-header" style="background:#f8fafc;">
        <i class="bi bi-eye" style="color:#64748b;"></i>
        <h6 style="color:#64748b;">Live Preview — How it appears across the CRM</h6>
    </div>
    <div class="settings-card-body">
        <div class="row g-3 align-items-center">
            {{-- Logo preview --}}
            <div class="col-auto">
                <div id="lp-logo-wrap" class="rounded-2 d-flex align-items-center justify-content-center"
                     style="width:56px;height:56px;background:{{ $themeColor ?? '#4f46e5' }};border-radius:12px!important;">
                    @if(setting_image('company_logo'))
                        <img id="lp-logo-img" src="{{ setting_image('company_logo') }}"
                             style="width:44px;height:44px;object-fit:contain;border-radius:8px;">
                    @else
                        <i class="bi bi-droplet-fill text-white fs-4" id="lp-logo-icon"></i>
                    @endif
                </div>
            </div>
            {{-- Name / tagline --}}
            <div class="col">
                <div id="lp-name" class="fw-700" style="font-size:16px;color:#1e293b;">
                    {{ $settings['company_name'] ?? 'WaterProof CRM' }}
                </div>
                <div id="lp-tagline" style="font-size:12px;color:#94a3b8;">
                    {{ $settings['company_tagline'] ?? 'Enterprise Management' }}
                </div>
                @if($settings['company_owner_name'] ?? '')
                <div id="lp-owner" style="font-size:12px;color:#6b7280;">Owner: {{ $settings['company_owner_name'] }}</div>
                @endif
            </div>
            {{-- Contact strip --}}
            <div class="col-12 col-md-auto">
                <div class="d-flex flex-wrap gap-3" style="font-size:12px;color:#64748b;">
                    @if($settings['company_mobile'] ?? '')
                    <span id="lp-mobile"><i class="bi bi-telephone me-1"></i>{{ $settings['company_mobile'] }}</span>
                    @endif
                    @if($settings['company_email'] ?? '')
                    <span id="lp-email"><i class="bi bi-envelope me-1"></i>{{ $settings['company_email'] }}</span>
                    @endif
                    @if($settings['company_website'] ?? '')
                    <span id="lp-website"><i class="bi bi-globe me-1"></i>{{ $settings['company_website'] }}</span>
                    @endif
                </div>
            </div>
        </div>
        {{-- Address strip --}}
        @if(($settings['company_address'] ?? '') || ($settings['company_city'] ?? ''))
        <div class="mt-2 pt-2 border-top" style="font-size:12px;color:#94a3b8;" id="lp-address">
            <i class="bi bi-geo-alt me-1"></i>
            {{ implode(', ', array_filter([$settings['company_address'] ?? '', $settings['company_city'] ?? '', $settings['company_state'] ?? '', $settings['company_country'] ?? ''])) }}
        </div>
        @endif
    </div>
</div>

<form id="form-general" action="{{ route('admin.settings.save', 'general') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- ── Branding ──────────────────────────────────────────────── --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="bi bi-image"></i><h6>Branding & Identity</h6>
        </div>
        <div class="settings-card-body">
            <div class="row g-4">

                {{-- Company Logo --}}
                <div class="col-md-6">
                    <label class="settings-label">Company Logo</label>
                    <div class="img-upload-wrap">
                        <input type="file" name="company_logo" class="img-file-input"
                               data-preview="prev-company-logo" accept="image/*">
                        <div class="upload-icon"><i class="bi bi-cloud-upload"></i></div>
                        <div class="upload-text">Click or drag to upload</div>
                        <div class="upload-hint">PNG, JPG, SVG — max 2MB</div>
                    </div>
                    <div class="img-preview-wrap" id="prev-company-logo"
                         style="{{ ($settings['company_logo'] ?? '') ? '' : 'display:none' }}">
                       <img src="{{ asset(setting_image('company_logo')) }}" alt="Logo">
                        <button type="button" class="btn-remove-img"
                                data-key="company_logo" data-preview="prev-company-logo">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>

                {{-- Favicon --}}
                <div class="col-md-6">
                    <label class="settings-label">Company Favicon</label>
                    <div class="img-upload-wrap">
                        <input type="file" name="company_favicon" class="img-file-input"
                               data-preview="prev-favicon" accept="image/*">
                        <div class="upload-icon"><i class="bi bi-cloud-upload"></i></div>
                        <div class="upload-text">Click or drag to upload</div>
                        <div class="upload-hint">ICO, PNG — 32×32 recommended</div>
                    </div>
                    <div class="img-preview-wrap" id="prev-favicon"
                         style="{{ ($settings['company_favicon'] ?? '') ? '' : 'display:none' }}">
                        <img src="{{ setting_image('company_favicon') }}" alt="Favicon">
                        <button type="button" class="btn-remove-img"
                                data-key="company_favicon" data-preview="prev-favicon">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>

                {{-- Name --}}
                <div class="col-md-6">
                    <label class="settings-label">Company Name <span class="text-danger">*</span></label>
                    <input type="text" name="company_name" id="inp-company-name"
                           class="settings-input" value="{{ $settings['company_name'] ?? '' }}"
                           placeholder="WaterProof CRM">
                </div>

                {{-- Tagline --}}
                <div class="col-md-6">
                    <label class="settings-label">Company Tagline</label>
                    <input type="text" name="company_tagline" id="inp-company-tagline"
                           class="settings-input" value="{{ $settings['company_tagline'] ?? '' }}"
                           placeholder="Enterprise Management">
                </div>

                {{-- Owner / Proprietor --}}
                <div class="col-md-6">
                    <label class="settings-label">Owner / Proprietor Name</label>
                    <input type="text" name="company_owner_name" class="settings-input"
                           value="{{ $settings['company_owner_name'] ?? '' }}" placeholder="John Doe">
                </div>

                {{-- Description --}}
                <div class="col-12">
                    <label class="settings-label">Company Description</label>
                    <textarea name="company_description" class="settings-input" rows="3"
                              placeholder="Brief description of your company…">{{ $settings['company_description'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Contact ────────────────────────────────────────────────── --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="bi bi-telephone"></i><h6>Contact Information</h6>
        </div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="settings-label">Mobile Number</label>
                    <div class="input-group">
                        <input type="text" name="company_mobile" id="inp-company-mobile"
                               class="settings-input rounded-start-0"
                               value="{{ $settings['company_mobile'] ?? '' }}" placeholder="+91 98765 43210">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Alternate Number</label>
                    <div class="input-group">
                        <input type="text" name="company_alt_mobile"
                               class="settings-input rounded-start-0"
                               value="{{ $settings['company_alt_mobile'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Email Address</label>
                    <div class="input-group">
                        <input type="email" name="company_email" id="inp-company-email"
                               class="settings-input rounded-start-0"
                               value="{{ $settings['company_email'] ?? '' }}" placeholder="info@company.com">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Website URL</label>
                    <div class="input-group">
                        <input type="url" name="company_website" id="inp-company-website"
                               class="settings-input rounded-start-0"
                               value="{{ $settings['company_website'] ?? '' }}" placeholder="https://www.company.com">
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="settings-label">WhatsApp Number</label>
                    <div class="input-group">
                        <input type="text" name="whatsapp_number"
                               class="settings-input rounded-start-0"
                               value="{{ $settings['whatsapp_number'] ?? '' }}" placeholder="+91 98765 43210">
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── GST & Legal ─────────────────────────────────────────────── --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="bi bi-patch-check"></i><h6>GST & Legal Details</h6>
        </div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="settings-label">GST Registration Type</label>
                    <select name="company_gst_type" class="settings-input" id="inp-gst-type">
                        @php $gstType = $settings['company_gst_type'] ?? 'regular'; @endphp
                        <option value="regular"      {{ $gstType === 'regular'      ? 'selected' : '' }}>Regular</option>
                        <option value="composite"    {{ $gstType === 'composite'    ? 'selected' : '' }}>Composite</option>
                        <option value="unregistered" {{ $gstType === 'unregistered' ? 'selected' : '' }}>Unregistered</option>
                    </select>
                    <div class="mt-2">
                        <span class="gst-badge {{ $gstType }}" id="gst-type-badge">
                            <i class="bi bi-patch-check-fill"></i>
                            {{ ucfirst($gstType) }}
                        </span>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="settings-label">GST Number</label>
                    <input type="text" name="company_gst" class="settings-input"
                           value="{{ $settings['company_gst'] ?? '' }}" placeholder="22AAAAA0000A1Z5"
                           maxlength="15" style="text-transform:uppercase;font-family:monospace;">
                    <span class="settings-hint"><i class="bi bi-info-circle"></i> 15-character GSTIN</span>
                </div>
                <div class="col-md-4">
                    <label class="settings-label">PAN Number</label>
                    <input type="text" name="company_pan" class="settings-input"
                           value="{{ $settings['company_pan'] ?? '' }}" placeholder="AAAAA0000A"
                           maxlength="10" style="text-transform:uppercase;font-family:monospace;">
                </div>
                <div class="col-md-6">
                    <label class="settings-label">CIN Number</label>
                    <input type="text" name="company_cin" class="settings-input"
                           value="{{ $settings['company_cin'] ?? '' }}" placeholder="U12345MH2020PTC123456"
                           style="text-transform:uppercase;font-family:monospace;">
                    <span class="settings-hint"><i class="bi bi-info-circle"></i> Corporate Identity Number</span>
                </div>
                <div class="col-md-6">
                    <label class="settings-label">MSME / Udyam Number</label>
                    <input type="text" name="company_msme" class="settings-input"
                           value="{{ $settings['company_msme'] ?? '' }}" placeholder="UDYAM-XX-00-0000000"
                           style="text-transform:uppercase;font-family:monospace;">
                    <span class="settings-hint"><i class="bi bi-info-circle"></i> Udyam Registration Number</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Bank Details — Primary ──────────────────────────────────── --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="bi bi-bank"></i><h6>Bank Details — Primary Account</h6>
        </div>
        <div class="settings-card-body">
            {{-- Live bank card preview --}}
            <div class="bank-card mb-4" id="bank-preview-1">
                <div class="bc-accent"></div>
                <div class="bc-chip"></div>
                <div class="bc-number" id="bp1-number">
                    {{ ($settings['bank_account_number'] ?? '') ? '•••• •••• ' . substr($settings['bank_account_number'], -4) : '•••• •••• •••• ••••' }}
                </div>
                <div class="bc-row">
                    <div>
                        <div class="bc-label">Account Holder</div>
                        <div class="bc-value" id="bp1-name">{{ $settings['bank_account_name'] ?? '—' }}</div>
                    </div>
                    <div class="text-end">
                        <div class="bc-bank-name" id="bp1-bank">{{ $settings['bank_name'] ?? '—' }}</div>
                        <div class="bc-ifsc" id="bp1-ifsc">{{ $settings['bank_ifsc'] ?? '' }}</div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <label class="settings-label">Bank Name</label>
                    <input type="text" name="bank_name" id="inp-bank-name" class="settings-input"
                           value="{{ $settings['bank_name'] ?? '' }}" placeholder="State Bank of India">
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Account Holder Name</label>
                    <input type="text" name="bank_account_name" id="inp-bank-acname" class="settings-input"
                           value="{{ $settings['bank_account_name'] ?? '' }}" placeholder="Company Legal Name">
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Account Number</label>
                    <input type="text" name="bank_account_number" id="inp-bank-acno" class="settings-input"
                           value="{{ $settings['bank_account_number'] ?? '' }}" placeholder="00000000000000"
                           style="font-family:monospace;letter-spacing:1px;">
                </div>
                <div class="col-md-3">
                    <label class="settings-label">IFSC Code</label>
                    <input type="text" name="bank_ifsc" id="inp-bank-ifsc" class="settings-input"
                           value="{{ $settings['bank_ifsc'] ?? '' }}" placeholder="SBIN0001234"
                           style="text-transform:uppercase;font-family:monospace;">
                </div>
                <div class="col-md-3">
                    <label class="settings-label">Account Type</label>
                    <select name="bank_account_type" class="settings-input">
                        @php $acType = $settings['bank_account_type'] ?? 'current'; @endphp
                        <option value="current" {{ $acType === 'current' ? 'selected' : '' }}>Current</option>
                        <option value="savings" {{ $acType === 'savings' ? 'selected' : '' }}>Savings</option>
                        <option value="od"      {{ $acType === 'od'      ? 'selected' : '' }}>OD / CC</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Branch Name</label>
                    <input type="text" name="bank_branch" class="settings-input"
                           value="{{ $settings['bank_branch'] ?? '' }}" placeholder="MG Road Branch">
                </div>
                <div class="col-md-6">
                    <label class="settings-label">SWIFT / BIC Code <span style="font-weight:400;color:#94a3b8;">(optional)</span></label>
                    <input type="text" name="bank_swift" class="settings-input"
                           value="{{ $settings['bank_swift'] ?? '' }}" placeholder="SBININBB"
                           style="text-transform:uppercase;font-family:monospace;">
                </div>
            </div>

            {{-- UPI --}}
            <div class="settings-divider mt-4">UPI / QR Payment</div>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="settings-label">UPI ID</label>
                    <div class="settings-input-group">
                        <span class="sig-prefix"><i class="bi bi-phone"></i></span>
                        <input type="text" name="bank_upi_id" id="inp-upi-id" class=""
                               value="{{ $settings['bank_upi_id'] ?? '' }}" placeholder="company@upi">
                    </div>
                    @if($settings['bank_upi_id'] ?? '')
                    <div class="upi-box mt-3">
                        <div class="upi-icon"><i class="bi bi-phone"></i></div>
                        <div>
                            <div class="upi-label">UPI ID</div>
                            <div class="upi-id" id="upi-display">{{ $settings['bank_upi_id'] }}</div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <label class="settings-label">UPI QR Code Image</label>
                    <div class="img-upload-wrap">
                        <input type="file" name="bank_qr_code" class="img-file-input"
                               data-preview="prev-bank-qr" accept="image/*">
                        <div class="upload-icon"><i class="bi bi-qr-code"></i></div>
                        <div class="upload-text">Upload QR Code</div>
                        <div class="upload-hint">PNG, JPG — max 2MB</div>
                    </div>
                    <div class="img-preview-wrap" id="prev-bank-qr"
                         style="{{ ($settings['bank_qr_code'] ?? '') ? '' : 'display:none' }}">
                        <img src="{{ setting_image('bank_qr_code') }}" alt="QR Code">
                        <button type="button" class="btn-remove-img"
                                data-key="bank_qr_code" data-preview="prev-bank-qr">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>



    {{-- ── Address ─────────────────────────────────────────────────── --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="bi bi-geo-alt"></i><h6>Address</h6>
        </div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-12">
                    <label class="settings-label">Street Address</label>
                    <textarea name="company_address" class="settings-input" rows="2"
                              placeholder="123, Main Street…">{{ $settings['company_address'] ?? '' }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="settings-label">City</label>
                    <input type="text" name="company_city" class="settings-input"
                           value="{{ $settings['company_city'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="settings-label">State</label>
                    <input type="text" name="company_state" class="settings-input"
                           value="{{ $settings['company_state'] ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="settings-label">Pincode</label>
                    <input type="text" name="company_pincode" class="settings-input"
                           value="{{ $settings['company_pincode'] ?? '' }}">
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Country</label>
                    <input type="text" name="company_country" class="settings-input"
                           value="{{ $settings['company_country'] ?? 'India' }}">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Locale ──────────────────────────────────────────────────── --}}
    <div class="settings-card">
        <div class="settings-card-header">
            <i class="bi bi-globe"></i><h6>Locale & Regional</h6>
        </div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="settings-label">Timezone</label>
                    <select name="timezone" class="settings-input">
                        @php $tz = $settings['timezone'] ?? 'Asia/Kolkata'; @endphp
                        @foreach(\DateTimeZone::listIdentifiers() as $zone)
                        <option value="{{ $zone }}" {{ $tz === $zone ? 'selected' : '' }}>{{ $zone }}</option>
                        @endforeach
                    </select>
                    <span class="settings-hint">Applied globally — affects all date/time displays.</span>
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Date Format</label>
                    <select name="date_format" class="settings-input">
                        @php $df = $settings['date_format'] ?? 'd/m/Y'; @endphp
                        @foreach(['d/m/Y' => '31/12/2025', 'm/d/Y' => '12/31/2025', 'Y-m-d' => '2025-12-31', 'd-m-Y' => '31-12-2025', 'd M Y' => '31 Dec 2025', 'd M, Y' => '31 Dec, 2025'] as $fmt => $ex)
                        <option value="{{ $fmt }}" {{ $df === $fmt ? 'selected' : '' }}>{{ $fmt }} — {{ $ex }}</option>
                        @endforeach
                    </select>
                    <span class="settings-hint">Used in tables, PDFs, and all date displays.</span>
                </div>
            </div>
        </div>
    </div>

  

    {{-- Save Bar --}}
    <div class="settings-save-bar">
        <span class="save-status" id="save-status-general"></span>
        <button type="button" id="btn-save-general" class="btn btn-primary px-4"
                onclick="saveSettings('general')">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
    </div>
</form>

@push('scripts')
<script>
// Live preview updates as user types
$('#inp-company-name').on('input', function () {
    $('#lp-name').text($(this).val() || 'WaterProof CRM');
});
$('#inp-company-tagline').on('input', function () {
    $('#lp-tagline').text($(this).val() || 'Enterprise Management');
});
$('#inp-company-mobile').on('input', function () {
    $('#lp-mobile').html('<i class="bi bi-telephone me-1"></i>' + ($(this).val() || ''));
});
$('#inp-company-email').on('input', function () {
    $('#lp-email').html('<i class="bi bi-envelope me-1"></i>' + ($(this).val() || ''));
});
$('#inp-company-website').on('input', function () {
    $('#lp-website').html('<i class="bi bi-globe me-1"></i>' + ($(this).val() || ''));
});

// GST badge live update
$('#inp-gst-type').on('change', function () {
    const v = $(this).val();
    const labels = { regular: 'Regular', composite: 'Composite', unregistered: 'Unregistered' };
    $('#gst-type-badge').attr('class', 'gst-badge ' + v).html('<i class="bi bi-patch-check-fill"></i> ' + (labels[v] || v));
});

// Bank 1 live preview
$('#inp-bank-name').on('input',    function () { $('#bp1-bank').text($(this).val() || '—'); });
$('#inp-bank-acname').on('input',  function () { $('#bp1-name').text($(this).val() || '—'); });
$('#inp-bank-ifsc').on('input',    function () { $('#bp1-ifsc').text($(this).val()); });
$('#inp-bank-acno').on('input',    function () {
    const v = $(this).val();
    $('#bp1-number').text(v.length >= 4 ? '•••• •••• ' + v.slice(-4) : '•••• •••• •••• ••••');
});

// Bank 2 live preview
$('#inp-bank2-name').on('input',   function () { $('#bp2-bank').text($(this).val() || '—'); });
$('#inp-bank2-acname').on('input', function () { $('#bp2-name').text($(this).val() || '—'); });
$('#inp-bank2-ifsc').on('input',   function () { $('#bp2-ifsc').text($(this).val()); });
$('#inp-bank2-acno').on('input',   function () {
    const v = $(this).val();
    $('#bp2-number').text(v.length >= 4 ? '•••• •••• ' + v.slice(-4) : '•••• •••• •••• ••••');
});

// Update sidebar brand name after save
// Update sidebar brand name and logo preview after save
$(document).on('settings:saved:general', function () {
    const name = $('#inp-company-name').val();
    if (name) {
        document.querySelector('.brand-text h4') && (document.querySelector('.brand-text h4').textContent = name);
    }
    // Update logo preview
    const logoImg = $('#lp-logo-img');
    const logoWrap = $('#prev-company-logo');
    // Preview new logo when file selected
    $(document).on('change', 'input[name=company_logo]', function(){
        var file = this.files[0];
        if(file){
            var reader = new FileReader();
            reader.onload = function(e){
                logoWrap.show().find('img').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        } else {
            logoWrap.hide();
        }
    });
    // Remove logo handler
    $('.btn-remove-img[data-key=company_logo]').on('click', function(){
        logoWrap.hide().find('img').attr('src','');
        $('input[name=company_logo]').val('');
    });
    const logoUrl = '{{ asset(setting_image('company_logo')) }}';
    if (logoUrl && logoUrl.trim() && logoUrl.indexOf('data:image') !== 0) {
        // Show preview with new logo
        logoWrap.show();
        logoWrap.find('img').attr('src', logoUrl);
    } else {
        // Hide preview if no logo
        logoWrap.hide();
    }
});
</script>
@endpush
