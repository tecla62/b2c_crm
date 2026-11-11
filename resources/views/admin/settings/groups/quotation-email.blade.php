{{-- ── Quotation Email Settings ─────────────────────────────────── --}}
<div class="settings-section-title"><i class="bi bi-envelope-heart me-2 text-primary"></i>Quotation Email Settings</div>
<p class="settings-section-sub">Configure automatic email sending when quotation status changes to "Sent".</p>

<form id="form-quotation-email" action="{{ route('admin.settings.save', 'quotation-email') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Auto-Send Configuration --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-send-check"></i><h6>Auto-Send on Status Change</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="settings-label">
                        <div class="form-check form-switch">
                            <input type="checkbox" name="quotation_auto_send_enabled" class="form-check-input" value="1" {{ setting('quotation_auto_send_enabled', '') == '1' ? 'checked' : '' }}>
                            <span class="form-check-label fw-600">Enable Auto-Send</span>
                        </div>
                    </label>
                    <span class="settings-hint">When enabled, emails will be sent automatically when status changes to "Sent".</span>
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Default Email Subject</label>
                    <input type="text" name="quotation_email_subject" class="settings-input" 
                           value="{{ setting('quotation_email_subject', 'Quotation {' . '{quotation_number}} from {' . '{company_name}}') }}" 
                           placeholder="Quotation {​{quotation_number}} from {​{company_name}}">
                    <span class="settings-hint">Available placeholders: {​{quotation_number}}, {​{company_name}}, {​{customer_name}}, {​{total}}</span>
                </div>
                <div class="col-md-12">
                    <label class="settings-label">Default Email Body</label>
                    <textarea name="quotation_email_body" class="settings-input" rows="4" 
                              placeholder="Dear {​{customer_name}},&#10;&#10;Please find your Quotation {​{quotation_number}} attached.&#10;&#10;Total Amount: {​{total}}&#10;&#10;Kindly review and revert at your earliest convenience.&#10;&#10;Regards,&#10;{​{company_name}}">{{ setting('quotation_email_body', "Dear {​{customer_name}},\n\nPlease find your Quotation {​{quotation_number}} attached.\n\nTotal Amount: {​{total}}\n\nKindly review and revert at your earliest convenience.\n\nRegards,\n{​{company_name}}") }}</textarea>
                    <span class="settings-hint">Available placeholders: {​{customer_name}}, {​{customer_company}}, {​{quotation_number}}, {​{quotation_date}}, {​{valid_until}}, {​{subtotal}}, {​{discount}}, {​{tax}}, {​{total}}, {​{company_name}}</span>
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Include PDF with Descriptions</label>
                    <select name="quotation_email_include_desc" class="settings-input">
                        <option value="1" {{ setting('quotation_email_include_desc', '1') == '1' ? 'selected' : '' }}>Yes - Include item descriptions in PDF</option>
                        <option value="0" {{ setting('quotation_email_include_desc', '1') != '1' ? 'selected' : '' }}>No - Exclude descriptions from PDF</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="settings-label">CC Email (Optional)</label>
                    <input type="email" name="quotation_email_cc" class="settings-input" 
                           value="{{ setting('quotation_email_cc', '') }}" 
                           placeholder="manager@company.com">
                    <span class="settings-hint">Comma-separated emails to CC on all quotation sends</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Email Template --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-palette"></i><h6>Email Template</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-md-12">
                    <label class="settings-label">Template Style</label>
                    <select name="quotation_email_template" class="settings-input" id="templateSelect" onchange="toggleTemplateEditor()">
                        <option value="default" {{ setting('quotation_email_template', 'default') == 'default' ? 'selected' : '' }}>Default Professional Template</option>
                        <option value="custom" {{ setting('quotation_email_template', 'default') != 'default' ? 'selected' : '' }}>Custom HTML Template</option>
                    </select>
                </div>
                <div class="col-md-12" id="customTemplateSection" style="display:none;">
                    <label class="settings-label">Custom HTML Template</label>
                    <textarea name="quotation_email_custom_template" class="settings-input font-monospace" rows="12" 
                              placeholder="<div style='font-family: Arial, sans-serif;'>...</div>">{{ setting('quotation_email_template', 'default') != 'default' ? setting('quotation_email_template', '') : '' }}</textarea>
                    <span class="settings-hint">Use placeholders like {​{customer_name}}, {​{quotation_number}}, {​{items_table}}, {​{totals}}, {​{total}}, etc.</span>
                </div>
                <div class="col-md-12" id="defaultTemplatePreview">
                    <div class="alert alert-info mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Default Template:</strong> Uses the professional email template with company branding, 
                        itemized table, totals summary, and footer. The email body you configure above will be included 
                        as the main message content.
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Test Email Section --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-send"></i><h6>Test Quotation Email</h6></div>
        <div class="settings-card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="settings-label">Select Quotation</label>
                    <select name="test_quotation_id" class="settings-input" id="testQuotationSelect">
                        <option value="">-- Select Quotation --</option>
                        @foreach(\App\Models\Quotation::with('customer')->latest()->limit(10)->get() as $q)
                            <option value="{{ $q->id }}">{{ $q->quotation_number }} - {{ $q->customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="settings-label">Test Email Address</label>
                    <input type="email" name="test_email" class="settings-input" id="testQuotationEmail" 
                           placeholder="test@example.com" value="{{ auth()->user()->email }}">
                </div>
                <div class="col-md-4">
                    <button type="button" class="btn btn-outline-primary w-100" id="btnTestQuotationEmail" onclick="sendTestQuotationEmail()">
                        <i class="bi bi-send me-2"></i>Send Test Email
                    </button>
                </div>
            </div>
            <div id="testQuotationResult" class="mt-3" style="display:none;"></div>
        </div>
    </div>

    <div class="settings-save-bar">
        <span class="save-status" id="save-status-quotation-email"></span>
        <button type="button" id="btn-save-quotation-email" class="btn btn-primary px-4" onclick="saveSettings('quotation-email')">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
    </div>
</form>

@push('scripts')
<script>
function toggleTemplateEditor() {
    const select = document.getElementById('templateSelect');
    const customSection = document.getElementById('customTemplateSection');
    const defaultPreview = document.getElementById('defaultTemplatePreview');
    
    if (select.value === 'custom') {
        customSection.style.display = 'block';
        defaultPreview.style.display = 'none';
    } else {
        customSection.style.display = 'none';
        defaultPreview.style.display = 'block';
    }
}

function sendTestQuotationEmail() {
    const quotationId = document.getElementById('testQuotationSelect').value;
    const testEmail = document.getElementById('testQuotationEmail').value.trim();
    
    if (!quotationId) {
        toastr.warning('Please select a quotation.');
        return;
    }
    if (!testEmail) {
        toastr.warning('Please enter a test email address.');
        return;
    }
    
    const btn = document.getElementById('btnTestQuotationEmail');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending…';
    
    $.post('{{ route("admin.settings.quotation-email.test") }}', {
        quotation_id: quotationId,
        test_email: testEmail,
        _token: '{{ csrf_token() }}'
    }, function(res) {
        $('#testQuotationResult').show().html(
            `<div class="alert alert-success py-2 mb-0"><i class="bi bi-check-circle me-2"></i>${res.message}</div>`
        );
        toastr.success(res.message);
    }).fail(function(xhr) {
        const msg = xhr.responseJSON?.message || 'Failed to send test email.';
        $('#testQuotationResult').show().html(
            `<div class="alert alert-danger py-2 mb-0"><i class="bi bi-x-circle me-2"></i>${msg}</div>`
        );
        toastr.error(msg);
    }).always(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-2"></i>Send Test Email';
    });
}

// Initialize on load
toggleTemplateEditor();
</script>
@endpush
