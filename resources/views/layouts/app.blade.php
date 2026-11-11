<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — {{ $appName }}</title>

    {{-- Dynamic Favicon --}}
    @if($appFavicon)
        <link rel="icon" type="image/png" href="{{ $appFavicon }}">
    @else
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>💧</text></svg>">
    @endif

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Vendor CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- CRM External CSS -->
    <link rel="stylesheet" href="{{ asset('css/crm.css') }}">

    {{-- ── Dynamic Theme from Settings ──────────────────────────────────── --}}
    <style>
        :root {
            --primary:      {{ $themeColor }};
            --primary-dark: {{ $themeColor }};
            --sidebar-bg:   {{ $sidebarColor }};
            --header-bg:    {{ $headerColor }};
        }
        /* Sidebar background */
        .sidebar { background: var(--sidebar-bg) !important; }
        .sb-brand { background: var(--sidebar-bg) !important; }

        /* Active & hover nav items */
        .sb-item:hover                { background: rgba(255,255,255,.06) !important; color: #fff !important; }
        .sb-item.active               { background: rgba(79,70,229,.18) !important; color: #fff !important; }
        .sb-item.active .sb-item-icon { background: var(--primary) !important; box-shadow: 0 3px 10px rgba(79,70,229,.45) !important; }
        .sb-item.active::after        { background: var(--primary) !important; }
        .sb-logo-icon                 { background: linear-gradient(135deg, var(--primary), #818cf8) !important; }

        /* Header background */
        .top-header { background: var(--header-bg) !important; }
        .top-header .profile-btn img,
        .top-header .profile-avatar {
            width: 36px !important;
            height: 36px !important;
            min-width: 36px !important;
            max-width: 36px !important;
            min-height: 36px !important;
            max-height: 36px !important;
            border-radius: 50% !important;
            object-fit: cover !important;
            flex-shrink: 0 !important;
            display: inline-block !important;
        }

        /* Primary buttons */
        .btn-primary { background: var(--primary) !important; border-color: var(--primary) !important; }
        .btn-primary:hover { filter: brightness(.9); }
        .btn-outline-primary { color: var(--primary) !important; border-color: var(--primary) !important; }
        .btn-outline-primary:hover { background: var(--primary) !important; color: #fff !important; }

        /* Pagination active */
        .pagination .page-item.active .page-link {
            background: var(--primary) !important;
            border-color: var(--primary) !important;
        }

        /* Settings nav active */
        .settings-nav-item.active {
            color: var(--primary) !important;
            border-left-color: var(--primary) !important;
            background: rgba(255,255,255,.06) !important;
        }

        /* Breadcrumb active */
        .breadcrumb-item.active { color: var(--primary) !important; }

        /* Focus rings */
        .form-control:focus, .form-select:focus, .settings-input:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--primary) 20%, transparent) !important;
        }

        /* Toggle checked */
        .form-switch .form-check-input:checked { background-color: var(--primary) !important; }

        /* Font size from settings */
        @php $fs = $globalSettings['font_size'] ?? 'medium'; @endphp
        @if($fs === 'small')  body { font-size: 12px !important; }
        @elseif($fs === 'large') body { font-size: 16px !important; }
        @else body { font-size: 14px !important; }
        @endif
    </style>

    @stack('styles')

    {{-- Apply dark mode INSTANTLY before render to prevent white flash --}}
    <script>
        (function(){
            var stored = localStorage.getItem('crm_dark_mode');
            var defaultDark = {{ ($globalSettings['dark_mode_default'] ?? '0') == '1' ? 'true' : 'false' }};
            if (stored === '1' || (stored === null && defaultDark)) {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>
</head>
<body class="{{ ($globalSettings['dark_mode_default'] ?? '0') == '1' ? 'dark-mode' : '' }}">

<!-- Mobile overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- ===================== SIDEBAR ===================== -->
<aside class="sidebar" id="sidebar">

    {{-- ── Brand (FIXED — never scrolls) ── --}}
    <div class="sb-brand">
        @php
            $brandLogo = setting_image('sidebar_logo')
                      ?: setting_image('company_logo')
                      ?: ($appLogo ?? '')
                      ?: (auth()->user()?->avatar ? auth()->user()->avatar_url : '');
        @endphp
        @if($brandLogo)
            <a href="{{ route('admin.dashboard') }}" class="sb-logo-box text-decoration-none">
                <img src="{{ $brandLogo }}" alt="{{ $appName }}">
            </a>
        @else
            <a href="{{ route('admin.dashboard') }}" class="sb-logo-icon text-decoration-none text-white">
                <i class="bi bi-droplet-fill"></i>
            </a>
        @endif
        <button class="sb-toggle" id="sbToggleBtn" title="Toggle sidebar">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>

    {{-- ── Scrollable body ── --}}
    <div class="sb-body">

        {{-- User strip --}}
        <a href="{{ route('admin.profile') }}" class="sb-user">
            <img src="{{ auth()->user()->avatar_url }}" alt="" class="sb-avatar">
            <div class="sb-user-info">
                <div class="sb-user-name">{{ auth()->user()->name }}</div>
                <div class="sb-user-role">{{ auth()->user()->role_name }}</div>
            </div>
            <span class="sb-online"></span>
        </a>

        {{-- Navigation --}}
        <nav class="sb-nav">

            <div class="sb-label">Main</div>

            <a href="{{ route('admin.dashboard') }}"
               class="sb-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-speedometer2"></i></span>
                <span class="sb-item-label">Dashboard</span>
                <span class="sb-tip">Dashboard</span>
            </a>

            @if(auth()->user()->hasPermission('users.view'))
            <a href="{{ route('admin.users.index') }}"
               class="sb-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-people"></i></span>
                <span class="sb-item-label">User Management</span>
                <span class="sb-tip">User Management</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('roles.view'))
            <a href="{{ route('admin.roles.index') }}"
               class="sb-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-shield-lock"></i></span>
                <span class="sb-item-label">Roles & Permissions</span>
                <span class="sb-tip">Roles & Permissions</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('customers.view') || auth()->user()->hasPermission('services.view') || auth()->user()->hasPermission('estimations.view') || auth()->user()->hasPermission('invoices.view') || auth()->user()->hasPermission('transactions.view') || auth()->user()->hasPermission('expenses.view') || auth()->user()->hasPermission('assets.view'))
            <div class="sb-label">Business</div>
            @endif

            @if(auth()->user()->hasPermission('customers.view'))
            <a href="{{ route('admin.customers.index') }}"
               class="sb-item {{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-person-badge"></i></span>
                <span class="sb-item-label">Customers</span>
                <span class="sb-tip">Customers</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('services.view'))
            <a href="{{ route('admin.services.index') }}"
               class="sb-item {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-grid"></i></span>
                <span class="sb-item-label">Services</span>
                <span class="sb-tip">Services</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('estimations.view'))
            <a href="{{ route('admin.quotations.index') }}"
               class="sb-item {{ request()->routeIs('admin.quotations.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-file-earmark-text"></i></span>
                <span class="sb-item-label">Quotations</span>
                <span class="sb-tip">Quotations</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('invoices.view'))
            <a href="{{ route('admin.invoices.index') }}"
               class="sb-item {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-receipt"></i></span>
                <span class="sb-item-label">Invoices</span>
                <span class="sb-tip">Invoices</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('transactions.view'))
            <a href="{{ route('admin.transactions.index') }}"
               class="sb-item {{ request()->routeIs('admin.transactions.*') && !request()->routeIs('admin.transactions.customer-ledger') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-cash-stack"></i></span>
                <span class="sb-item-label">Payments</span>
                <span class="sb-tip">Payments</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('expenses.view'))
            <a href="{{ route('admin.expenses.index') }}"
               class="sb-item {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-wallet2"></i></span>
                <span class="sb-item-label">Expenses</span>
                <span class="sb-tip">Expenses</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('assets.view'))
            <a href="{{ route('admin.assets.index') }}"
               class="sb-item {{ request()->routeIs('admin.assets.index') || request()->routeIs('admin.assets.create') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-box-seam"></i></span>
                <span class="sb-item-label">Assets</span>
                <span class="sb-tip">Assets</span>
            </a>
            <a href="{{ route('admin.asset-assignments.index') }}"
               class="sb-item {{ request()->routeIs('admin.asset-assignments.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-arrow-left-right"></i></span>
                <span class="sb-item-label">Assign Assets</span>
                <span class="sb-tip">Assign Assets</span>
            </a>
            <a href="{{ route('admin.assets.history') }}"
               class="sb-item {{ request()->routeIs('admin.assets.history') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-clock-history"></i></span>
                <span class="sb-item-label">Asset History</span>
                <span class="sb-tip">Asset History</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('transactions.view'))
            <a href="{{ route('admin.transactions.customer-ledger') }}"
               class="sb-item {{ request()->routeIs('admin.transactions.customer-ledger') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-person-lines-fill"></i></span>
                <span class="sb-item-label">Customer Ledger</span>
                <span class="sb-tip">Customer Ledger</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('employees.view') || auth()->user()->hasPermission('attendance.view') || auth()->user()->hasPermission('payroll.view'))
            <div class="sb-label">Staff & Payroll</div>
            @endif

            @if(auth()->user()->hasPermission('employees.view'))
            <a href="{{ route('admin.employees.index') }}"
               class="sb-item {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-people-fill"></i></span>
                <span class="sb-item-label">Employees / Staff</span>
                <span class="sb-tip">Employees / Staff</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('attendance.view'))
            <a href="{{ route('admin.attendance.index') }}"
               class="sb-item {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-calendar-check"></i></span>
                <span class="sb-item-label">Daily Attendance & OT</span>
                <span class="sb-tip">Daily Attendance & OT</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('payroll.view'))
            <a href="{{ route('admin.payrolls.index') }}"
               class="sb-item {{ request()->routeIs('admin.payrolls.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-cash-coin"></i></span>
                <span class="sb-item-label">Weekly & Payroll</span>
                <span class="sb-tip">Weekly & Payroll</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('reports.view'))
            <a href="{{ route('admin.reports.index') }}"
               class="sb-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-graph-up"></i></span>
                <span class="sb-item-label">Reports</span>
                <span class="sb-tip">Reports</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('activity_logs.view') || auth()->user()->hasPermission('settings.view'))
            <div class="sb-label">System</div>
            @endif

            @if(auth()->user()->hasPermission('activity_logs.view'))
            <a href="{{ route('admin.activity-logs.index') }}"
               class="sb-item {{ request()->routeIs('admin.activity-logs.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-clock-history"></i></span>
                <span class="sb-item-label">Activity Logs</span>
                <span class="sb-tip">Activity Logs</span>
            </a>
            <a href="{{ route('admin.login-history.index') }}"
               class="sb-item {{ request()->routeIs('admin.login-history.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-door-open"></i></span>
                <span class="sb-item-label">Login History</span>
                <span class="sb-tip">Login History</span>
            </a>
            @endif

            @if(auth()->user()->hasPermission('settings.view'))
            <a href="{{ route('admin.settings.index') }}"
               class="sb-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <span class="sb-item-icon"><i class="bi bi-gear"></i></span>
                <span class="sb-item-label">Settings</span>
                <span class="sb-tip">Settings</span>
            </a>
            @endif

        </nav>

        {{-- Footer --}}
        <div class="sb-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="sb-item">
                    <span class="sb-item-icon"><i class="bi bi-box-arrow-right"></i></span>
                    <span class="sb-item-label">Logout</span>
                    <span class="sb-tip">Logout</span>
                </button>
            </form>
        </div>

    </div>{{-- end sb-body --}}

</aside>
<!-- =================== END SIDEBAR =================== -->



<!-- =================== MAIN CONTENT =================== -->
<div class="main-content">

    <!-- Top Header -->
    <header class="top-header" style="background:{{ $headerColor }};">
        <div class="header-left">
            <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
                <i class="bi bi-list"></i>
            </button>
            <div class="header-search d-none d-md-block" id="globalSearch">
                <i class="bi bi-search"></i>
                <input type="text" id="globalSearchInput" placeholder="Search invoices, customers... (Ctrl+K)" aria-label="Search" autocomplete="off">
                <div class="global-search-results" id="globalSearchResults"></div>
            </div>
        </div>

        <div class="header-right">
            <button class="notif-btn me-1" id="darkModeToggle" aria-label="Toggle Dark Mode" title="Toggle Dark Mode">
                <i class="bi bi-moon"></i>
            </button>
            
            @php $initialUnreadNotifications = auth()->user()->unreadNotifications()->count(); @endphp
            <div class="dropdown notif-dropdown-wrap">
                <button class="notif-btn" id="notifDropdownBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Notifications">
                    <i class="bi bi-bell"></i>
                    <span class="notif-badge" id="notifBadge" style="{{ $initialUnreadNotifications ? '' : 'display:none' }}">
                        {{ $initialUnreadNotifications > 9 ? '9+' : $initialUnreadNotifications }}
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notif-menu" aria-labelledby="notifDropdownBtn">
                    <div class="notif-head">
                        <div>
                            <div class="notif-title">Notifications</div>
                            <div class="notif-sub" id="notifSubtitle">{{ $initialUnreadNotifications }} unread</div>
                        </div>
                        <button type="button" class="notif-action" id="notifMarkAllBtn">Mark all read</button>
                    </div>
                    <div class="notif-list" id="notifList">
                        <div class="notif-state">Loading notifications...</div>
                    </div>
                    <div class="notif-foot">
                        <button type="button" class="notif-action" id="notifRefreshBtn"><i class="bi bi-arrow-clockwise"></i> Refresh</button>
                        <button type="button" class="notif-action text-danger" id="notifClearReadBtn">Clear read</button>
                    </div>
                </div>
            </div>

            <div class="dropdown">
                <a href="#" class="profile-btn text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ auth()->user()->avatar_url }}" alt="Avatar" class="profile-avatar">
                    <div class="profile-info d-none d-md-block">
                        <div class="p-name">{{ auth()->user()->name }}</div>
                        <div class="p-role">{{ auth()->user()->role_name }}</div>
                    </div>
                    <i class="bi bi-chevron-down fs-12 text-muted d-none d-md-inline"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.profile') }}">
                            <i class="bi bi-person"></i> My Profile
                        </a>
                    </li>
                    @if(auth()->user()->hasPermission('settings.view'))
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.settings.index') }}">
                            <i class="bi bi-gear"></i> Settings
                        </a>
                    </li>
                    @endif
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Page Content -->
    <div class="content-wrapper" style="flex: 1 0 auto; width: 100%; display: flow-root;">
        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="text-center py-3 border-top" style="font-size:12px;color:#94a3b8;clear:both;width:100%;margin-top:auto;position:relative;z-index:1;background:inherit;">
        &copy; {{ date('Y') }} {{ $appName }}.
        @if(setting('company_website'))
            <a href="{{ setting('company_website') }}" target="_blank" class="text-muted text-decoration-none ms-1">{{ setting('company_website') }}</a>
        @endif
        @if(setting('company_email'))
            &nbsp;|&nbsp; <a href="mailto:{{ setting('company_email') }}" class="text-muted text-decoration-none">{{ setting('company_email') }}</a>
        @endif
    </footer>

</div>
<!-- ================= END MAIN CONTENT ================= -->

<!-- Vendor JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Flash messages
    @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Success!', text: '{{ addslashes(session('success')) }}', timer: 3000, showConfirmButton: false });
    @endif
    @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Error!', text: '{{ addslashes(session('error')) }}', timer: 3000, showConfirmButton: false });
    @endif
    @if(session('warning'))
        Swal.fire({ icon: 'warning', title: 'Warning!', text: '{{ addslashes(session('warning')) }}', timer: 3000, showConfirmButton: false });
    @endif

    // ── Sidebar ───────────────────────────────────────────────────────────
    const sidebar      = document.getElementById('sidebar');
    const overlay      = document.getElementById('sidebarOverlay');
    const headerBtn    = document.getElementById('sidebarToggle');  // top-bar hamburger
    const inlineToggle = document.getElementById('sbToggleBtn');    // inside brand row
    const railTab      = document.getElementById('sidebarRailTab');  // side handle/tab
    const isMobile     = () => window.innerWidth < 992;

    // Restore collapsed state on desktop page load
    if (!isMobile() && localStorage.getItem('sbMini') === '1')
        document.body.classList.add('sb-mini');

    function syncSidebarTab() {
        if (!railTab) return;
        const icon = railTab.querySelector('i');
        const label = railTab.querySelector('span');
        const mobile = isMobile();
        const open = sidebar.classList.contains('show');
        const mini = document.body.classList.contains('sb-mini');

        railTab.classList.toggle('is-open', mobile && open);
        railTab.setAttribute('aria-expanded', mobile ? String(open) : String(!mini));
        railTab.setAttribute('title', mobile ? (open ? 'Close menu' : 'Open menu') : (mini ? 'Expand sidebar' : 'Collapse sidebar'));
        if (icon) icon.className = `bi ${mobile ? (open ? 'bi-x-lg' : 'bi-list') : (mini ? 'bi-chevron-right' : 'bi-chevron-left')}`;
        if (label) label.textContent = mobile ? 'Menu' : (mini ? 'Open' : 'Close');
    }

    function toggleMini() {
        const mini = document.body.classList.toggle('sb-mini');
        localStorage.setItem('sbMini', mini ? '1' : '0');
        syncSidebarTab();
    }
    function openMobile()  { sidebar.classList.add('show');  overlay.classList.add('show'); syncSidebarTab(); }
    function closeMobile() { sidebar.classList.remove('show'); overlay.classList.remove('show'); syncSidebarTab(); }

    // Top-bar hamburger: slide on mobile, collapse on desktop
    headerBtn.addEventListener('click', () => isMobile() ? openMobile() : toggleMini());

    // Inline toggle button inside brand row (desktop)
    inlineToggle && inlineToggle.addEventListener('click', toggleMini);
    railTab && railTab.addEventListener('click', () => isMobile()
        ? (sidebar.classList.contains('show') ? closeMobile() : openMobile())
        : toggleMini()
    );

    // Close mobile sidebar on overlay click
    overlay.addEventListener('click', closeMobile);

    // Close mobile sidebar after choosing a menu item
    sidebar.querySelectorAll('a.sb-item, .sb-user').forEach(link => {
        link.addEventListener('click', () => { if (isMobile()) closeMobile(); });
    });

    // Clean up mobile state on resize
    window.addEventListener('resize', () => { if (!isMobile()) closeMobile(); syncSidebarTab(); });
    syncSidebarTab();

    window.CRM_THEME_COLOR = '{{ $themeColor }}';

    // Global header search
    (() => {
        const wrap = document.getElementById('globalSearch');
        const input = document.getElementById('globalSearchInput');
        const panel = document.getElementById('globalSearchResults');
        if (!wrap || !input || !panel) return;

        const searchUrl = '{{ route('admin.global-search') }}';
        let timer = null;
        let controller = null;
        let results = [];
        let activeIndex = -1;

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[char]));

        const setState = (message) => {
            panel.innerHTML = `<div class="global-search-state">${message}</div>`;
            panel.classList.add('show');
        };

        const render = () => {
            if (!results.length) {
                setState('No results found');
                return;
            }

            panel.innerHTML = results.map((item, index) => `
                <a href="${escapeHtml(item.url)}" class="global-search-item ${index === activeIndex ? 'active' : ''}" data-index="${index}">
                    <span class="global-search-icon"><i class="bi ${escapeHtml(item.icon || 'bi-search')}"></i></span>
                    <span class="global-search-copy">
                        <span class="global-search-title">${escapeHtml(item.title)}</span>
                        <span class="global-search-sub">${escapeHtml(item.type)}${item.subtitle ? ' - ' + escapeHtml(item.subtitle) : ''}</span>
                    </span>
                </a>
            `).join('');
            panel.classList.add('show');
        };

        const runSearch = () => {
            const q = input.value.trim();
            activeIndex = -1;

            if (q.length < 2) {
                results = [];
                panel.classList.remove('show');
                panel.innerHTML = '';
                return;
            }

            controller?.abort();
            controller = new AbortController();
            setState('Searching...');

            fetch(`${searchUrl}?q=${encodeURIComponent(q)}`, {
                headers: { 'Accept': 'application/json' },
                signal: controller.signal
            })
                .then(response => response.ok ? response.json() : Promise.reject(new Error('Search failed')))
                .then(data => {
                    results = Array.isArray(data.results) ? data.results : [];
                    render();
                })
                .catch(error => {
                    if (error.name !== 'AbortError') setState('Search is unavailable');
                });
        };

        input.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(runSearch, 220);
        });

        input.addEventListener('focus', () => {
            if (input.value.trim().length >= 2 && panel.innerHTML) panel.classList.add('show');
        });

        input.addEventListener('keydown', (event) => {
            if (!panel.classList.contains('show')) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                activeIndex = Math.min(activeIndex + 1, results.length - 1);
                render();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                render();
            } else if (event.key === 'Enter' && activeIndex >= 0 && results[activeIndex]) {
                event.preventDefault();
                window.location.href = results[activeIndex].url;
            } else if (event.key === 'Escape') {
                panel.classList.remove('show');
                input.blur();
            }
        });

        panel.addEventListener('mouseover', (event) => {
            const item = event.target.closest('.global-search-item');
            if (!item) return;
            activeIndex = Number(item.dataset.index);
            render();
        });

        document.addEventListener('click', (event) => {
            if (!wrap.contains(event.target)) panel.classList.remove('show');
        });
    })();

    // Header notifications
    (() => {
        const list = document.getElementById('notifList');
        const badge = document.getElementById('notifBadge');
        const subtitle = document.getElementById('notifSubtitle');
        const dropdownBtn = document.getElementById('notifDropdownBtn');
        const markAllBtn = document.getElementById('notifMarkAllBtn');
        const clearReadBtn = document.getElementById('notifClearReadBtn');
        const refreshBtn = document.getElementById('notifRefreshBtn');
        if (!list || !badge || !dropdownBtn) return;

        const urls = {
            index: '{{ route('admin.notifications.index') }}',
            read: '{{ url('/admin/notifications') }}',
            readAll: '{{ route('admin.notifications.read-all') }}',
            clearRead: '{{ route('admin.notifications.clear-read') }}',
        };

        const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
        }[char]));

        const setBadge = (count) => {
            const safeCount = Number(count || 0);
            badge.textContent = safeCount > 9 ? '9+' : safeCount;
            badge.style.display = safeCount > 0 ? 'flex' : 'none';
            if (subtitle) subtitle.textContent = `${safeCount} unread`;
        };

        const setState = (message) => {
            list.innerHTML = `<div class="notif-state">${message}</div>`;
        };

        const render = (items) => {
            if (!items.length) {
                setState('You are all caught up.');
                return;
            }

            list.innerHTML = items.map((item) => `
                <button type="button" class="notif-item ${item.read ? '' : 'unread'}" data-id="${escapeHtml(item.id)}" data-url="${escapeHtml(item.url || '')}">
                    <span class="notif-icon"><i class="bi ${escapeHtml(item.icon || 'bi-bell')}"></i></span>
                    <span class="notif-copy">
                        <span class="notif-item-title">${escapeHtml(item.title)}</span>
                        <span class="notif-item-msg">${escapeHtml(item.message)}</span>
                        <span class="notif-item-time">${escapeHtml(item.time || '')}</span>
                    </span>
                    ${item.read ? '' : '<span class="notif-dot"></span>'}
                </button>
            `).join('');
        };

        const loadNotifications = () => {
            setState('Loading notifications...');
            fetch(urls.index, { headers: { 'Accept': 'application/json' } })
                .then(response => response.ok ? response.json() : Promise.reject(new Error('Failed')))
                .then(data => {
                    setBadge(data.unread_count || 0);
                    render(Array.isArray(data.notifications) ? data.notifications : []);
                })
                .catch(() => setState('Notifications are unavailable right now.'));
        };

        const post = (url) => fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            },
        }).then(response => response.ok ? response.json() : Promise.reject(new Error('Failed')));

        list.addEventListener('click', (event) => {
            const item = event.target.closest('.notif-item');
            if (!item) return;
            const id = item.dataset.id;
            const url = item.dataset.url;

            post(`${urls.read}/${encodeURIComponent(id)}/read`)
                .then(data => {
                    setBadge(data.unread_count || 0);
                    if (url) window.location.href = url;
                    else loadNotifications();
                })
                .catch(() => {
                    if (url) window.location.href = url;
                });
        });

        markAllBtn?.addEventListener('click', () => {
            post(urls.readAll).then(() => loadNotifications()).catch(() => setState('Could not update notifications.'));
        });

        clearReadBtn?.addEventListener('click', () => {
            post(urls.clearRead).then(() => loadNotifications()).catch(() => setState('Could not clear notifications.'));
        });

        refreshBtn?.addEventListener('click', loadNotifications);
        dropdownBtn.addEventListener('shown.bs.dropdown', loadNotifications);

        setTimeout(loadNotifications, 600);
    })();

    // ── UX Enhancements ───────────────────────────────────────────────────
    (() => {
        // Dark Mode Toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        if (darkModeToggle) {
            const icon = darkModeToggle.querySelector('i');

            // Sync body class from localStorage (in case head script ran on <html>)
            const stored = localStorage.getItem('crm_dark_mode');
            @php $defaultDark = ($globalSettings['dark_mode_default'] ?? '0') == '1'; @endphp
            const defaultDark = {{ $defaultDark ? 'true' : 'false' }};
            const isDarkOnLoad = stored === '1' || (stored === null && defaultDark);

            if (isDarkOnLoad) {
                document.body.classList.add('dark-mode');
                icon.classList.remove('bi-moon');
                icon.classList.add('bi-sun');
            } else {
                document.body.classList.remove('dark-mode');
                icon.classList.remove('bi-sun');
                icon.classList.add('bi-moon');
            }

            darkModeToggle.addEventListener('click', () => {
                const isDark = document.body.classList.toggle('dark-mode');
                icon.classList.toggle('bi-sun', isDark);
                icon.classList.toggle('bi-moon', !isDark);
                localStorage.setItem('crm_dark_mode', isDark ? '1' : '0');
            });
        }

        // Global Keyboard Shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl+K or Cmd+K for Global Search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.getElementById('globalSearchInput');
                if (searchInput) searchInput.focus();
            }
        });

        // Global Form Submit Loading States
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!this.checkValidity()) {
                    return; // Allow native validation tooltips to appear
                }
                const btn = this.querySelector('button[type="submit"]');
                if (btn && !btn.hasAttribute('data-no-loading')) {
                    const originalHtml = btn.innerHTML;
                    btn.style.pointerEvents = 'none'; // Prevent double submit immediately
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading...';
                    
                    // Delay setting disabled attribute so standard POST is never cancelled by Chrome/WebKit
                    setTimeout(() => {
                        btn.setAttribute('disabled', 'true');
                    }, 50);

                    // Safety watchdog: reset after 6 seconds in case submission is blocked or aborted
                    setTimeout(() => {
                        btn.removeAttribute('disabled');
                        btn.style.pointerEvents = '';
                        btn.innerHTML = originalHtml;
                    }, 6000);
                }
            });
        });
    })();
</script>

@stack('scripts')
</body>
</html>
