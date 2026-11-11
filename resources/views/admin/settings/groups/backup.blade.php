{{-- ── Backup Settings ──────────────────────────────────────── --}}
<div class="settings-section-title"><i class="bi bi-cloud-arrow-down me-2 text-primary"></i>Backup Settings</div>
<p class="settings-section-sub">Manage database backups and configure automatic backup schedules.</p>

<form id="form-backup" action="{{ route('admin.settings.save', 'backup') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Manual Backup --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-database"></i><h6>Manual Backup</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="backup-action-card">
                        <i class="bi bi-database-down"></i>
                        <h6>Database Backup</h6>
                        <p>Download a full SQL dump of your database</p>
                        <a href="{{ route('admin.settings.backup') }}" class="btn btn-primary btn-sm mt-3 w-100">
                            <i class="bi bi-download me-2"></i>Download Backup
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="backup-action-card">
                        <i class="bi bi-arrow-clockwise"></i>
                        <h6>Clear Cache</h6>
                        <p>Clear application and settings cache</p>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-3 w-100" id="btnClearCacheBackup">
                            <i class="bi bi-arrow-clockwise me-2"></i>Clear Cache
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="backup-action-card">
                        <i class="bi bi-info-circle text-warning"></i>
                        <h6>Restore Backup</h6>
                        <p>Restore from a previous SQL backup file</p>
                        <button type="button" class="btn btn-outline-warning btn-sm mt-3 w-100" disabled>
                            <i class="bi bi-upload me-2"></i>Coming Soon
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Auto Backup --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-calendar-check"></i><h6>Automatic Backup</h6></div>
        <div class="settings-card-body">
            <div class="toggle-wrap mb-4">
                <div class="toggle-info">
                    <div class="toggle-label">Enable Auto Backup</div>
                    <div class="toggle-desc">Automatically backup database on a schedule</div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="auto_backup" value="1" {{ ($settings['auto_backup'] ?? '0') == '1' ? 'checked' : '' }}>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="settings-label">Backup Frequency</label>
                    <select name="backup_frequency" class="settings-input">
                        @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $val => $lbl)
                        <option value="{{ $val }}" {{ ($settings['backup_frequency'] ?? 'daily') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="settings-label">Retention Days</label>
                    <input type="number" name="backup_retention" class="settings-input" value="{{ $settings['backup_retention'] ?? '7' }}" min="1" max="365">
                    <span class="settings-hint">Delete backups older than N days</span>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-save-bar">
        <span class="save-status" id="save-status-backup"></span>
        <button type="button" id="btn-save-backup" class="btn btn-primary px-4" onclick="saveSettings('backup')">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
    </div>
</form>

@push('scripts')
<script>
$('#btnClearCacheBackup').on('click', function () {
    $.post('{{ route('admin.settings.cache.clear') }}', {}, res => {
        toastr.success(res.message);
    }).fail(() => toastr.error('Failed to clear cache.'));
});
</script>
@endpush
