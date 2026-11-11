@extends('layouts.app')

@section('title', 'Settings')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/settings.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css">
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Global Settings</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Settings</li>
            </ol>
        </nav>
    </div>
    <button class="btn btn-outline-secondary btn-sm" id="btnClearCache">
        <i class="bi bi-arrow-clockwise me-1"></i>Clear Cache
    </button>
</div>

<div class="settings-layout">

    {{-- ── Sidebar Nav ─────────────────────────────────────────── --}}
    <aside class="settings-nav">
        <div class="settings-nav-header">Settings Menu</div>

        @php
        $navItems = [
            ['group' => 'general',      'icon' => 'bi-building',          'label' => 'Company Profile'],
            ['group' => 'invoice',      'icon' => 'bi-receipt',           'label' => 'Invoice Settings'],
            ['group' => 'estimation',   'icon' => 'bi-calculator',        'label' => 'Estimation Settings'],
            ['group' => 'pdf',          'icon' => 'bi-file-earmark-pdf',  'label' => 'PDF & Documents'],
            ['group' => 'mail',         'icon' => 'bi-envelope',          'label' => 'Mail / SMTP'],
            ['group' => 'theme',        'icon' => 'bi-palette',           'label' => 'Theme & UI'],
            ['group' => 'login',        'icon' => 'bi-box-arrow-in-right','label' => 'Login Page'],
            ['group' => 'notification', 'icon' => 'bi-bell',              'label' => 'Notifications'],
            ['group' => 'security',     'icon' => 'bi-shield-lock',       'label' => 'Security'],
            ['group' => 'backup',       'icon' => 'bi-cloud-arrow-down',  'label' => 'Backup'],
        ];
        @endphp

        @foreach($navItems as $item)
        <a href="{{ route('admin.settings.group', $item['group']) }}"
           class="settings-nav-item {{ $group === $item['group'] ? 'active' : '' }}">
            <i class="bi {{ $item['icon'] }}"></i>
            {{ $item['label'] }}
        </a>
        @endforeach
    </aside>

    {{-- ── Settings Panel ──────────────────────────────────────── --}}
    <div class="settings-panel">
        @include('admin.settings.groups.' . $group)
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js"></script>
<script>
toastr.options = {
    positionClass: 'toast-top-right',
    timeOut: 3500,
    progressBar: true,
    closeButton: true,
};

// ── Generic AJAX form save ─────────────────────────────────────────────────
function saveSettings(group) {
    const form     = document.getElementById('form-' + group);
    const formData = new FormData(form);
    const btn      = document.getElementById('btn-save-' + group);
    const status   = document.getElementById('save-status-' + group);

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving…';
    if (status) status.textContent = '';

    $.ajax({
        url: form.action,
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success(res) {
            toastr.success(res.message);
            if (status) status.innerHTML = '<i class="bi bi-check-circle text-success me-1"></i>Saved just now';

            // Fire group-specific event for live UI updates
            $(document).trigger('settings:saved:' + group);

            // Live update sidebar brand for general settings
            if (group === 'general') {
                const name    = $('[name="company_name"]').val();
                const tagline = $('[name="company_tagline"]').val();
                if (name)    $('.brand-text h4').text(name);
                if (tagline) $('.brand-text span').text(tagline);
                // Update page title
                document.title = document.title.replace(/— .+$/, '— ' + name);
            }

            // Live update theme color CSS variable
            if (group === 'theme') {
                const color   = $('[name="theme_color"]').val();
                const sidebar = $('[name="sidebar_color"]').val();
                const header  = $('[name="header_color"]').val();
                if (color)   document.documentElement.style.setProperty('--primary', color);
                if (sidebar) document.documentElement.style.setProperty('--sidebar-bg', sidebar);
                if (header)  document.documentElement.style.setProperty('--header-bg', header);
                if (sidebar) document.querySelector('.sidebar').style.background = sidebar;
                if (header)  document.querySelector('.top-header').style.background = header;
            }
        },
        error(xhr) {
            const msg = xhr.responseJSON?.message || 'Something went wrong.';
            toastr.error(msg);
        },
        complete() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Save Changes';
        }
    });
}

// ── Image preview on file select ───────────────────────────────────────────
$(document).on('change', '.img-file-input', function () {
    const file      = this.files[0];
    const previewId = $(this).data('preview');
    if (!file || !previewId) return;
    const reader = new FileReader();
    reader.onload = e => {
        const wrap = $('#' + previewId);
        wrap.find('img').attr('src', e.target.result);
        wrap.show();
    };
    reader.readAsDataURL(file);
});

// ── Remove image ───────────────────────────────────────────────────────────
$(document).on('click', '.btn-remove-img', function () {
    const key       = $(this).data('key');
    const previewId = $(this).data('preview');
    $.post('{{ route('admin.settings.image.delete') }}', { key }, res => {
        if (res.success) {
            $('#' + previewId).hide();
            toastr.success('Image removed.');
        }
    });
});

// ── Color picker ↔ hex input sync ─────────────────────────────────────────
$(document).on('input', 'input[type="color"]', function () {
    $(this).siblings('.color-hex').val($(this).val());
});
$(document).on('input', '.color-hex', function () {
    const val = $(this).val();
    if (/^#[0-9A-Fa-f]{6}$/.test(val)) $(this).siblings('input[type="color"]').val(val);
});

// ── Clear cache ────────────────────────────────────────────────────────────
$('#btnClearCache').on('click', function () {
    const btn = $(this);
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Clearing…');
    $.post('{{ route('admin.settings.cache.clear') }}', {}, res => {
        toastr.success(res.message);
    }).fail(() => toastr.error('Failed to clear cache.'))
      .always(() => btn.prop('disabled', false).html('<i class="bi bi-arrow-clockwise me-1"></i>Clear Cache'));
});
</script>
@endpush
