{{-- ── Mail / SMTP Settings ─────────────────────────────────── --}}
<div class="settings-section-title"><i class="bi bi-envelope me-2 text-primary"></i>Mail / SMTP Settings</div>
<p class="settings-section-sub">Configure outgoing email server for notifications, invoices and estimations.</p>

<form id="form-mail" action="{{ route('admin.settings.save', 'mail') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-server"></i><h6>SMTP Configuration</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-md-8">
                    <label class="settings-label">Mail Host</label>
                    <input type="text" name="mail_host" class="settings-input" value="{{ $settings['mail_host'] ?? '' }}" placeholder="smtp.mailtrap.io">
                </div>
                <div class="col-md-4">
                    <label class="settings-label">Mail Port</label>
                    <input type="number" name="mail_port" class="settings-input" value="{{ $settings['mail_port'] ?? '587' }}" placeholder="587">
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Mail Username</label>
                    <input type="text" name="mail_username" class="settings-input" value="{{ $settings['mail_username'] ?? '' }}" placeholder="your@email.com">
                </div>
                <div class="col-md-6">
                    <label class="settings-label">Mail Password</label>
                    <div class="position-relative">
                        <input type="password" name="mail_password" id="mailPassword" class="settings-input" value="{{ $settings['mail_password'] ?? '' }}" placeholder="••••••••">
                        <button type="button" class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2 border-0 bg-transparent text-muted" onclick="toggleMailPass()">
                            <i class="bi bi-eye" id="mailPassIcon"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="settings-label">Encryption</label>
                    <select name="mail_encryption" class="settings-input">
                        @foreach(['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'] as $val => $lbl)
                        <option value="{{ $val }}" {{ ($settings['mail_encryption'] ?? 'tls') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="settings-label">From Email</label>
                    <input type="email" name="mail_from_address" class="settings-input" value="{{ $settings['mail_from_address'] ?? '' }}" placeholder="noreply@company.com">
                </div>
                <div class="col-md-4">
                    <label class="settings-label">From Name</label>
                    <input type="text" name="mail_from_name" class="settings-input" value="{{ $settings['mail_from_name'] ?? '' }}" placeholder="WaterProof CRM">
                </div>
            </div>
        </div>
    </div>

    {{-- Test Email --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-send"></i><h6>Send Test Email</h6></div>
        <div class="settings-card-body">
            <div class="mail-test-box">
                <h6><i class="bi bi-info-circle me-2 text-primary"></i>Verify your SMTP configuration by sending a test email.</h6>
                <div class="row g-3 align-items-end">
                    <div class="col-md-7">
                        <label class="settings-label">Test Email Address</label>
                        <input type="email" id="testEmailInput" class="settings-input" placeholder="test@example.com">
                    </div>
                    <div class="col-md-5">
                        <button type="button" class="btn btn-outline-primary w-100" id="btnTestMail">
                            <i class="bi bi-send me-2"></i>Send Test Email
                        </button>
                    </div>
                </div>
                <div id="testMailResult" class="mt-3" style="display:none;"></div>
            </div>
        </div>
    </div>

    <div class="settings-save-bar">
        <span class="save-status" id="save-status-mail"></span>
        <button type="button" id="btn-save-mail" class="btn btn-primary px-4" onclick="saveSettings('mail')">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
    </div>
</form>

@push('scripts')
<script>
function toggleMailPass() {
    const inp  = document.getElementById('mailPassword');
    const icon = document.getElementById('mailPassIcon');
    if (inp.type === 'password') { inp.type = 'text'; icon.className = 'bi bi-eye-slash'; }
    else { inp.type = 'password'; icon.className = 'bi bi-eye'; }
}

$('#btnTestMail').on('click', function () {
    const email = $('#testEmailInput').val().trim();
    if (!email) { toastr.warning('Please enter a test email address.'); return; }

    const btn = $(this);
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Sending…');

    $.post('{{ route('admin.settings.mail.test') }}', { test_email: email }, function (res) {
        $('#testMailResult').show().html(
            `<div class="alert alert-success py-2 mb-0"><i class="bi bi-check-circle me-2"></i>${res.message}</div>`
        );
        toastr.success(res.message);
    }).fail(function (xhr) {
        const msg = xhr.responseJSON?.message || 'Failed to send test email.';
        $('#testMailResult').show().html(
            `<div class="alert alert-danger py-2 mb-0"><i class="bi bi-x-circle me-2"></i>${msg}</div>`
        );
        toastr.error(msg);
    }).always(function () {
        btn.prop('disabled', false).html('<i class="bi bi-send me-2"></i>Send Test Email');
    });
});
</script>
@endpush
