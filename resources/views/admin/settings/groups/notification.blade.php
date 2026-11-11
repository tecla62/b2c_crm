{{-- ── Notification Settings ────────────────────────────────── --}}
<div class="settings-section-title"><i class="bi bi-bell me-2 text-primary"></i>Notification Settings</div>
<p class="settings-section-sub">Control which notification channels are active for your CRM.</p>

<form id="form-notification" action="{{ route('admin.settings.save', 'notification') }}" method="POST" enctype="multipart/form-data">
    @csrf

    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-toggles"></i><h6>Notification Channels</h6></div>
        <div class="settings-card-body">
            @php
            $notifs = [
                'notif_email'    => ['bi-envelope',   'Email Notifications',   'Send notifications via email to users'],
                'notif_sms'      => ['bi-phone',      'SMS Notifications',     'Send SMS alerts for important events'],
                'notif_whatsapp' => ['bi-whatsapp',   'WhatsApp Notifications','Send WhatsApp messages for updates'],
                'notif_browser'  => ['bi-browser-chrome','Browser Notifications','Show browser push notifications'],
            ];
            @endphp

            @foreach($notifs as $key => [$icon, $label, $desc])
            <div class="toggle-wrap">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;background:#eef2ff;">
                        <i class="bi {{ $icon }} text-primary fs-5"></i>
                    </div>
                    <div class="toggle-info">
                        <div class="toggle-label">{{ $label }}</div>
                        <div class="toggle-desc">{{ $desc }}</div>
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1" {{ ($settings[$key] ?? '0') == '1' ? 'checked' : '' }}>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-bell"></i><h6>In-App Notification Test</h6></div>
        <div class="settings-card-body">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="toggle-label">Header Bell Notification</div>
                    <div class="toggle-desc">Send a test item to your own top-header notification center.</div>
                </div>
                <button type="button" class="btn btn-outline-primary" id="btnTestNotification" onclick="sendTestNotification()">
                    <i class="bi bi-send me-2"></i>Send Test
                </button>
            </div>
            <div id="testNotificationResult" class="mt-3" style="display:none;"></div>
        </div>
    </div>

    <div class="settings-save-bar">
        <span class="save-status" id="save-status-notification"></span>
        <button type="button" id="btn-save-notification" class="btn btn-primary px-4" onclick="saveSettings('notification')">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
    </div>
</form>

<script>
function sendTestNotification() {
    const btn = document.getElementById('btnTestNotification');
    const result = document.getElementById('testNotificationResult');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

    $.post('{{ route("admin.settings.notification.test") }}', {}, function(res) {
        result.style.display = 'block';
        result.innerHTML = `<div class="alert alert-success py-2 mb-0">${res.message}</div>`;
        document.getElementById('notifRefreshBtn')?.click();
    }).fail(function(xhr) {
        result.style.display = 'block';
        result.innerHTML = `<div class="alert alert-danger py-2 mb-0">${xhr.responseJSON?.message || 'Failed to send notification.'}</div>`;
    }).always(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-send me-2"></i>Send Test';
    });
}
</script>
