{{-- ── Theme & UI Settings ──────────────────────────────────── --}}
<div class="settings-section-title"><i class="bi bi-palette me-2 text-primary"></i>Theme & UI Settings</div>
<p class="settings-section-sub">Customize the look and feel of your CRM panel.</p>

<form id="form-theme" action="{{ route('admin.settings.save', 'theme') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- Colors --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-palette2"></i><h6>Color Scheme</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                @foreach(['theme_color' => ['Theme / Primary Color', '#4f46e5'], 'sidebar_color' => ['Sidebar Background', '#0f172a'], 'header_color' => ['Header Background', '#ffffff']] as $key => [$label, $default])
                <div class="col-md-4">
                    <label class="settings-label">{{ $label }}</label>
                    <div class="color-picker-wrap">
                        <input type="color" name="{{ $key }}" value="{{ $settings[$key] ?? $default }}">
                        <input type="text" class="color-hex" value="{{ $settings[$key] ?? $default }}" maxlength="7">
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Live Preview --}}
            <div class="mt-4 p-3 rounded-3 border" id="themePreview" style="background: {{ $settings['sidebar_color'] ?? '#0f172a' }};">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-2 px-3 py-2 text-white fw-600 fs-13" style="background: {{ $settings['theme_color'] ?? '#4f46e5' }};">
                        Primary Button
                    </div>
                    <span class="text-white fs-13 opacity-75">← Live Preview</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Display Options --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-toggles"></i><h6>Display Options</h6></div>
        <div class="settings-card-body">
            @php
            $toggles = [
                'dark_mode_default' => ['Dark Mode Default', 'Enable dark mode for all users by default'],
                'compact_sidebar'   => ['Compact Sidebar', 'Use a narrower sidebar with icon-only view'],
            ];
            @endphp
            @foreach($toggles as $key => [$label, $desc])
            <div class="toggle-wrap">
                <div class="toggle-info">
                    <div class="toggle-label">{{ $label }}</div>
                    <div class="toggle-desc">{{ $desc }}</div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="{{ $key }}" value="1" {{ ($settings[$key] ?? '0') == '1' ? 'checked' : '' }}>
                </div>
            </div>
            @endforeach

            <div class="row g-4 mt-1">
                <div class="col-md-6">
                    <label class="settings-label">Font Size</label>
                    <select name="font_size" class="settings-input">
                        @foreach(['small' => 'Small (12px)', 'medium' => 'Medium (14px)', 'large' => 'Large (16px)'] as $val => $lbl)
                        <option value="{{ $val }}" {{ ($settings['font_size'] ?? 'medium') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar Logo --}}
    <div class="settings-card">
        <div class="settings-card-header"><i class="bi bi-image"></i><h6>Sidebar Logo</h6></div>
        <div class="settings-card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="settings-label">Sidebar Logo</label>
                    <div class="img-upload-wrap">
                        <input type="file" name="sidebar_logo" class="img-file-input" data-preview="prev-sidebar_logo" accept="image/*">
                        <div class="upload-icon"><i class="bi bi-cloud-upload"></i></div>
                        <div class="upload-text">Click or drag to upload</div>
                        <div class="upload-hint">PNG, SVG — transparent background recommended</div>
                    </div>
                    <div class="img-preview-wrap" id="prev-sidebar_logo" style="{{ ($settings['sidebar_logo'] ?? '') ? '' : 'display:none' }}">
                        <img src="{{ setting_image('sidebar_logo') }}" alt="Sidebar Logo">
                        <button type="button" class="btn-remove-img" data-key="sidebar_logo" data-preview="prev-sidebar_logo"><i class="bi bi-x"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-save-bar">
        <span class="save-status" id="save-status-theme"></span>
        <button type="button" id="btn-save-theme" class="btn btn-primary px-4" onclick="saveSettings('theme')">
            <i class="bi bi-check-lg me-2"></i>Save Changes
        </button>
    </div>
</form>

@push('scripts')
<script>
// Live preview update
$(document).on('input', '[name="theme_color"]', function () {
    $('#themePreview .rounded-2').css('background', $(this).val());
});
$(document).on('input', '[name="sidebar_color"]', function () {
    $('#themePreview').css('background', $(this).val());
});
</script>
@endpush
