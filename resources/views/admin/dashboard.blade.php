@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
/* ── Design tokens ───────────────────────────────────────────────── */
:root {
    --bg:           #f1f5f9;
    --surface-1:    #ffffff;
    --surface-2:    #f8fafc;
    --surface-3:    #f1f5f9;
    --border:       rgba(0,0,0,.08);
    --border-hover: rgba(0,0,0,.15);
    --text-primary: #0f172a;
    --text-muted:   #64748b;
    --text-faint:   #94a3b8;
    --accent:       var(--primary, #3b82f6);
    --accent-glow:  rgba(59,130,246,.12);
    --green:        #16a34a;
    --amber:        #d97706;
    --red:          #dc2626;
    --purple:       #7c3aed;
    --teal:         #0d9488;
    --sky:          #0284c7;
    --radius-sm:    8px;
    --radius-md:    12px;
    --radius-lg:    16px;
    --radius-xl:    20px;
    --shadow-sm:    0 1px 3px rgba(0,0,0,.06);
    --shadow-md:    0 4px 16px rgba(0,0,0,.08);
    --shadow-lg:    0 8px 32px rgba(0,0,0,.10);
    --font:         'DM Sans', sans-serif;
    --mono:         'DM Mono', monospace;
    --transition:   all .2s cubic-bezier(.4,0,.2,1);
}

/* ── Base overrides ──────────────────────────────────────────────── */
body { font-family: var(--font); background: var(--bg); color: var(--text-primary); }

/* ── Scrollbar ───────────────────────────────────────────────────── */
::-webkit-scrollbar { width: 4px; height: 4px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 99px; }

/* ── Page header ─────────────────────────────────────────────────── */
.db-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 14px;
    padding: 20px 0 24px;
}
.db-title {
    font-size: clamp(18px, 4vw, 24px);
    font-weight: 700;
    color: var(--text-primary);
    letter-spacing: -.4px;
    margin: 0;
}
.db-subtitle {
    font-size: 13px;
    color: var(--text-muted);
    margin-top: 3px;
}
.db-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: 99px;
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
}
.db-badge .dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--green);
    box-shadow: 0 0 6px var(--green);
}

/* ── Period filter ───────────────────────────────────────────────── */
.period-filter {
    display: inline-flex;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    padding: 3px;
    gap: 2px;
}
.period-filter a {
    padding: 6px 13px;
    border-radius: 6px;
    text-decoration: none;
    color: var(--text-muted);
    font-size: 12px;
    font-weight: 600;
    transition: var(--transition);
    white-space: nowrap;
}
.period-filter a:hover { color: var(--text-primary); background: var(--surface-3); }
.period-filter a.active { background: var(--accent); color: #fff; box-shadow: 0 0 12px var(--accent-glow); }

/* ── KPI cards ───────────────────────────────────────────────────── */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
    margin-bottom: 20px;
}
.kpi-card {
    background: var(--surface-1);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    padding: 20px;
    position: relative;
    overflow: hidden;
    transition: var(--transition);
    cursor: default;
}
.kpi-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--kpi-accent,.2) 0%, transparent 60%);
    opacity: .06;
    pointer-events: none;
}
.kpi-card::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: var(--kpi-accent, var(--accent));
    border-radius: var(--radius-lg) var(--radius-lg) 0 0;
}
.kpi-card:hover {
    border-color: var(--border-hover);
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}
.kpi-icon {
    width: 36px; height: 36px;
    border-radius: var(--radius-sm);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 16px;
    background: color-mix(in srgb, var(--kpi-accent) 12%, transparent);
    color: var(--kpi-accent, var(--accent));
    margin-bottom: 14px;
}
.kpi-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .8px;
    color: var(--text-faint);
    margin-bottom: 6px;
}
.kpi-value {
    font-size: clamp(20px, 3vw, 26px);
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1;
    margin-bottom: 8px;
    font-variant-numeric: tabular-nums;
}
.kpi-sub {
    font-size: 12px;
    color: var(--text-muted);
    line-height: 1.4;
}

/* ── Cards ───────────────────────────────────────────────────────── */
.db-card {
    background: var(--surface-1);
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    height: 100%;
}
.db-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid var(--border);
}
.db-card-title {
    font-size: 13.5px;
    font-weight: 700;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}
.db-card-title i {
    color: var(--accent);
    font-size: 15px;
}
.db-card-body { padding: 20px; }
.db-card-body.p-0 { padding: 0; }

/* ── Chart container ─────────────────────────────────────────────── */
#businessTrendChart { min-height: 300px; }

/* ── Quick actions ───────────────────────────────────────────────── */
.quick-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}
.quick-action {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 12px;
    background: var(--surface-2);
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--text-muted);
    font-size: 12.5px;
    font-weight: 600;
    transition: var(--transition);
}
.quick-action:hover {
    border-color: var(--accent);
    color: var(--text-primary);
    background: var(--surface-3);
    transform: translateY(-1px);
}
.quick-action i {
    width: 30px; height: 30px;
    border-radius: var(--radius-sm);
    background: color-mix(in srgb, var(--accent) 12%, transparent);
    color: var(--accent);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

/* ── Pipeline ────────────────────────────────────────────────────── */
.pipeline-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
    font-size: 12.5px;
}
.pipeline-label strong { color: var(--text-primary); font-weight: 600; }
.pipeline-label span { color: var(--text-muted); font-size: 11.5px; }
.pipeline-bar {
    height: 6px;
    border-radius: 99px;
    background: var(--surface-3);
    overflow: hidden;
    display: flex;
    margin-bottom: 4px;
}
.pipeline-bar span { display: block; height: 100%; transition: width .6s ease; }
.pipeline-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 8px;
    margin-bottom: 20px;
}
.pipeline-legend span {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    color: var(--text-muted);
}
.pipeline-legend span::before {
    content: '';
    width: 8px; height: 8px;
    border-radius: 2px;
    background: var(--dot-color);
    flex-shrink: 0;
}
.status-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid var(--border);
    font-size: 12.5px;
}
.status-row:last-child { border-bottom: none; }
.status-row .label { color: var(--text-muted); }
.status-row .val { font-weight: 700; color: var(--text-primary); font-variant-numeric: tabular-nums; }

/* ── Mini rows ───────────────────────────────────────────────────── */
.mini-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 13px 20px;
    border-bottom: 1px solid var(--border);
    text-decoration: none;
    color: inherit;
    transition: var(--transition);
}
.mini-row:last-child { border-bottom: none; }
.mini-row:hover { background: var(--surface-2); }
.mini-icon {
    width: 36px; height: 36px;
    border-radius: var(--radius-sm);
    background: var(--surface-2);
    border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    color: var(--accent);
    font-size: 15px;
    flex-shrink: 0;
}
.mini-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--text-primary);
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.mini-sub {
    font-size: 11.5px;
    color: var(--text-muted);
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.money-pos { color: var(--green); font-weight: 700; font-size: 13px; font-variant-numeric: tabular-nums; white-space: nowrap; }
.money-neg { color: var(--red); font-weight: 700; font-size: 13px; font-variant-numeric: tabular-nums; white-space: nowrap; }

/* ── Table ───────────────────────────────────────────────────────── */
.db-table { width: 100%; border-collapse: collapse; }
.db-table thead tr { background: var(--surface-2); }
.db-table th {
    padding: 10px 16px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .7px;
    color: var(--text-faint);
    white-space: nowrap;
    border-bottom: 1px solid var(--border);
    text-align: left;
}
.db-table td {
    padding: 11px 16px;
    font-size: 12.5px;
    color: var(--text-muted);
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
.db-table tbody tr:last-child td { border-bottom: none; }
.db-table tbody tr:hover td { background: var(--surface-2); }
.db-table a { color: var(--accent); text-decoration: none; font-weight: 600; }
.db-table a:hover { text-decoration: underline; }
.db-table .text-end { text-align: right; }

/* ── Badges ──────────────────────────────────────────────────────── */
.badge-type {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .3px;
}
.badge-invoice { background: color-mix(in srgb, var(--sky) 14%, transparent); color: var(--sky); }
.badge-quotation { background: color-mix(in srgb, var(--purple) 14%, transparent); color: var(--purple); }

/* ── Customer avatar ─────────────────────────────────────────────── */
.customer-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--border);
    flex-shrink: 0;
}

/* ── Btn ─────────────────────────────────────────────────────────── */
.btn-ghost {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px;
    font-size: 12px;
    font-weight: 600;
    color: var(--text-muted);
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    text-decoration: none;
    transition: var(--transition);
}
.btn-ghost:hover {
    color: var(--text-primary);
    border-color: var(--border-hover);
    background: var(--surface-2);
}

/* ── Empty state ─────────────────────────────────────────────────── */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 36px 24px;
    color: var(--text-faint);
    font-size: 13px;
}
.empty-state i { font-size: 24px; opacity: .4; }

/* ── Activity card scroll ────────────────────────────────────────── */
.activity-scroll { max-height: 400px; overflow-y: auto; }

/* ── Mono numbers ────────────────────────────────────────────────── */
.num { font-family: var(--mono); font-variant-numeric: tabular-nums; }

/* ── Stock badge ─────────────────────────────────────────────────── */
.stock-warn {
    background: color-mix(in srgb, var(--amber) 14%, transparent);
    color: var(--amber);
    font-size: 11.5px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 99px;
    white-space: nowrap;
}

/* ── Section spacing ─────────────────────────────────────────────── */
.db-section { margin-bottom: 20px; }

/* ─────────────── MOBILE ────────────────────────────────────────── */
@media (max-width: 575px) {
    .kpi-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .kpi-card { padding: 14px; }
    .kpi-value { font-size: 18px; }
    .quick-grid { grid-template-columns: repeat(2, 1fr); }
    .db-header { padding: 14px 0 18px; }
    .period-filter { width: 100%; overflow-x: auto; }
    .db-badge { display: none; }
    .mini-row { padding: 11px 14px; gap: 10px; }
    .db-card-header { padding: 13px 14px; }
    .db-card-body { padding: 14px; }
    .db-table th, .db-table td { padding: 9px 12px; font-size: 12px; }
    /* Hide less critical table cols on mobile */
    .hide-xs { display: none !important; }
}
@media (max-width: 767px) {
    .quick-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 992px) {
    .col-xl-4-custom { flex: 0 0 auto; width: 33.333%; }
    .col-xl-8-custom { flex: 0 0 auto; width: 66.666%; }
}

/* ── Animations ──────────────────────────────────────────────────── */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
.kpi-card { animation: fadeUp .4s ease both; }
.kpi-card:nth-child(1) { animation-delay: .04s; }
.kpi-card:nth-child(2) { animation-delay: .08s; }
.kpi-card:nth-child(3) { animation-delay: .12s; }
.kpi-card:nth-child(4) { animation-delay: .16s; }
.kpi-card:nth-child(5) { animation-delay: .20s; }
.kpi-card:nth-child(6) { animation-delay: .24s; }
.kpi-card:nth-child(7) { animation-delay: .28s; }
.kpi-card:nth-child(8) { animation-delay: .32s; }
</style>
@endpush

@section('content')
@php
    $user = auth()->user();
    $canCustomers  = $user->hasPermission('customers.view');
    $canQuotations = $user->hasPermission('quotations.view') || $user->hasPermission('estimations.view');
    $canInvoices   = $user->hasPermission('invoices.view');
    $canPayments   = $user->hasPermission('transactions.view');
    $canExpenses   = $user->hasPermission('expenses.view');
    $canAssets     = $user->hasPermission('assets.view');
    $canServices   = $user->hasPermission('services.view');
    $canUsers      = $user->hasPermission('users.view');
@endphp

{{-- ── PAGE HEADER ──────────────────────────────────────────────── --}}
<div class="db-header">
    <div>
        <h1 class="db-title">Management Dashboard</h1>
        <div class="db-subtitle">{{ $periodLabel }} overview &mdash; {{ now()->format('D, d M Y') }}</div>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <div class="period-filter">
            @foreach(['month' => 'Month', 'quarter' => 'Quarter', 'year' => 'Year', 'all' => 'All'] as $key => $label)
                <a href="{{ route('admin.dashboard', ['period' => $key]) }}" class="{{ $period === $key ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>
        <div class="db-badge">
            <span class="dot"></span>
            {{ $appName }}
        </div>
    </div>
</div>

{{-- ── KPI GRID ─────────────────────────────────────────────────── --}}
<div class="kpi-grid db-section">
    @if($canCustomers)
    <div class="kpi-card" style="--kpi-accent: var(--sky)">
        <div class="kpi-icon"><i class="bi bi-people-fill"></i></div>
        <div class="kpi-label">Customers</div>
        <div class="kpi-value num">{{ number_format($stats['customers']) }}</div>
        <div class="kpi-sub">{{ number_format($stats['active_customers']) }} active</div>
    </div>
    @endif
    @if($canQuotations)
    <div class="kpi-card" style="--kpi-accent: var(--purple)">
        <div class="kpi-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
        <div class="kpi-label">Quotations</div>
        <div class="kpi-value num">{{ format_currency($stats['quotation_value']) }}</div>
        <div class="kpi-sub">{{ number_format($stats['quotations']) }} total &middot; {{ number_format($stats['approved_quotations']) }} approved</div>
    </div>
    @endif
    @if($canInvoices)
    <div class="kpi-card" style="--kpi-accent: var(--amber)">
        <div class="kpi-icon"><i class="bi bi-receipt-cutoff"></i></div>
        <div class="kpi-label">Invoice Value</div>
        <div class="kpi-value num">{{ format_currency($stats['invoice_value']) }}</div>
        <div class="kpi-sub">{{ number_format($stats['invoices']) }} invoices this {{ strtolower($periodLabel) }}</div>
    </div>
    @endif
    @if($canPayments)
    <div class="kpi-card" style="--kpi-accent: var(--green)">
        <div class="kpi-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="kpi-label">Collected</div>
        <div class="kpi-value num">{{ format_currency($stats['collected']) }}</div>
        <div class="kpi-sub">{{ format_currency($stats['pending_payments']) }} pending</div>
    </div>
    @endif
    @if($canExpenses)
    <div class="kpi-card" style="--kpi-accent: var(--red)">
        <div class="kpi-icon"><i class="bi bi-wallet2"></i></div>
        <div class="kpi-label">Expenses</div>
        <div class="kpi-value num">{{ format_currency($stats['expenses']) }}</div>
        <div class="kpi-sub">Net {{ format_currency($stats['net_balance']) }}</div>
    </div>
    @endif
    @if($canInvoices || $canPayments)
    <div class="kpi-card" style="--kpi-accent: var(--red)">
        <div class="kpi-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="kpi-label">Outstanding</div>
        <div class="kpi-value num">{{ format_currency($stats['outstanding']) }}</div>
        <div class="kpi-sub">Unpaid invoice balance</div>
    </div>
    @endif
    @if($canAssets)
    <div class="kpi-card" style="--kpi-accent: var(--teal)">
        <div class="kpi-icon"><i class="bi bi-boxes"></i></div>
        <div class="kpi-label">Asset Stock</div>
        <div class="kpi-value num">{{ number_format($stats['assets_available']) }}</div>
        <div class="kpi-sub">{{ number_format($stats['assets_issued']) }} issued &middot; {{ number_format($stats['open_assignments']) }} open</div>
    </div>
    @endif
    @if($canServices || $canUsers)
    <div class="kpi-card" style="--kpi-accent: #6366f1">
        <div class="kpi-icon"><i class="bi bi-grid-fill"></i></div>
        <div class="kpi-label">Services</div>
        <div class="kpi-value num">{{ number_format($stats['services']) }}</div>
        <div class="kpi-sub">{{ number_format($stats['active_users']) }}/{{ number_format($stats['users']) }} users active</div>
    </div>
    @endif
</div>

{{-- ── TREND CHART + QUICK ACTIONS ─────────────────────────────── --}}
<div class="row g-3 db-section">
    <div class="col-lg-8">
        <div class="db-card">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="bi bi-graph-up-arrow"></i>Revenue, Collection &amp; Expenses</h5>
            </div>
            <div class="db-card-body">
                <div id="businessTrendChart"></div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="db-card">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="bi bi-lightning-charge-fill"></i>Quick Actions</h5>
            </div>
            <div class="db-card-body">
                <div class="quick-grid">
                    @if($user->hasPermission('customers.create'))<a class="quick-action" href="{{ route('admin.customers.create') }}"><i class="bi bi-person-plus"></i>Add Customer</a>@endif
                    @if($user->hasPermission('quotations.create'))<a class="quick-action" href="{{ route('admin.quotations.create') }}"><i class="bi bi-file-earmark-plus"></i>New Quotation</a>@endif
                    @if($user->hasPermission('invoices.create'))<a class="quick-action" href="{{ route('admin.invoices.create') }}"><i class="bi bi-receipt-cutoff"></i>New Invoice</a>@endif
                    @if($user->hasPermission('transactions.create'))<a class="quick-action" href="{{ route('admin.transactions.create') }}"><i class="bi bi-cash-coin"></i>Record Payment</a>@endif
                    @if($user->hasPermission('expenses.create'))<a class="quick-action" href="{{ route('admin.expenses.create') }}"><i class="bi bi-wallet2"></i>Add Expense</a>@endif
                    @if($user->hasPermission('assets.create'))<a class="quick-action" href="{{ route('admin.asset-assignments.create') }}"><i class="bi bi-arrow-left-right"></i>Assign Assets</a>@endif
                    @if($canPayments)<a class="quick-action" href="{{ route('admin.transactions.customer-ledger') }}"><i class="bi bi-person-lines-fill"></i>Ledger</a>@endif
                    @if($user->hasPermission('settings.view'))<a class="quick-action" href="{{ route('admin.settings.index') }}"><i class="bi bi-sliders2"></i>Settings</a>@endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── PIPELINE + OUTSTANDING + ASSETS ─────────────────────────── --}}
<div class="row g-3 db-section">
    @if($canInvoices || $canPayments || $canQuotations)
    <div class="col-lg-4">
        <div class="db-card">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="bi bi-kanban"></i>Pipeline Health</h5>
            </div>
            <div class="db-card-body">
                @if($canQuotations)
                @php $qTotal = max(1, array_sum($pipeline['quotations'])); @endphp
                <div class="mb-3">
                    <div class="pipeline-label">
                        <strong>Quotations</strong>
                        <span>{{ array_sum($pipeline['quotations']) }} total</span>
                    </div>
                    <div class="pipeline-bar">
                        <span style="width:{{ ($pipeline['quotations']['draft']    / $qTotal)*100 }}%;background:#cbd5e1;"></span>
                        <span style="width:{{ ($pipeline['quotations']['sent']     / $qTotal)*100 }}%;background:var(--sky);"></span>
                        <span style="width:{{ ($pipeline['quotations']['approved'] / $qTotal)*100 }}%;background:var(--green);"></span>
                        <span style="width:{{ ($pipeline['quotations']['rejected'] / $qTotal)*100 }}%;background:var(--red);"></span>
                    </div>
                    <div class="pipeline-legend">
                        <span style="--dot-color:#cbd5e1">Draft</span>
                        <span style="--dot-color:var(--sky)">Sent</span>
                        <span style="--dot-color:var(--green)">Approved</span>
                        <span style="--dot-color:var(--red)">Rejected</span>
                    </div>
                </div>
                @endif
                @if($canInvoices)
                @php $iTotal = max(1, array_sum($pipeline['invoices'])); @endphp
                <div class="mb-3">
                    <div class="pipeline-label">
                        <strong>Invoices</strong>
                        <span>{{ array_sum($pipeline['invoices']) }} total</span>
                    </div>
                    <div class="pipeline-bar">
                        <span style="width:{{ ($pipeline['invoices']['draft']   / $iTotal)*100 }}%;background:#cbd5e1;"></span>
                        <span style="width:{{ ($pipeline['invoices']['sent']    / $iTotal)*100 }}%;background:var(--sky);"></span>
                        <span style="width:{{ ($pipeline['invoices']['partial'] / $iTotal)*100 }}%;background:var(--amber);"></span>
                        <span style="width:{{ ($pipeline['invoices']['paid']    / $iTotal)*100 }}%;background:var(--green);"></span>
                        <span style="width:{{ ($pipeline['invoices']['overdue'] / $iTotal)*100 }}%;background:var(--red);"></span>
                    </div>
                    <div class="pipeline-legend">
                        <span style="--dot-color:#cbd5e1">Draft</span>
                        <span style="--dot-color:var(--sky)">Sent</span>
                        <span style="--dot-color:var(--amber)">Partial</span>
                        <span style="--dot-color:var(--green)">Paid</span>
                        <span style="--dot-color:var(--red)">Overdue</span>
                    </div>
                </div>
                @endif
                @if($canPayments)
                <div class="status-row"><span class="label">Pending payments</span><span class="val num">{{ number_format($pipeline['payments']['pending']) }}</span></div>
                <div class="status-row"><span class="label">Cleared payments</span><span class="val num">{{ number_format($pipeline['payments']['cleared']) }}</span></div>
                <div class="status-row"><span class="label">Failed / cancelled</span><span class="val num">{{ number_format($pipeline['payments']['failed']) }}</span></div>
                @endif
            </div>
        </div>
    </div>
    @endif

    @if($canInvoices)
    <div class="col-lg-4">
        <div class="db-card">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="bi bi-exclamation-circle"></i>Outstanding Invoices</h5>
                <a href="{{ route('admin.invoices.index', ['status' => 'partial']) }}" class="btn-ghost">View all <i class="bi bi-chevron-right" style="font-size:10px;"></i></a>
            </div>
            <div class="p-0">
                @forelse($outstandingInvoices as $invoice)
                    <a href="{{ route('admin.invoices.show', $invoice) }}" class="mini-row">
                        <span class="mini-icon"><i class="bi bi-receipt"></i></span>
                        <span class="flex-grow-1 min-w-0">
                            <span class="mini-title">{{ $invoice->invoice_number }}</span>
                            <span class="mini-sub">{{ $invoice->customer?->name ?? 'No customer' }} &middot; Due {{ $invoice->due_date?->format($dateFormat) ?? 'N/A' }}</span>
                        </span>
                        <span class="money-neg">{{ format_currency($invoice->outstanding_amount) }}</span>
                    </a>
                @empty
                    <div class="empty-state"><i class="bi bi-check2-circle"></i>No outstanding invoices</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    @if($canAssets)
    <div class="col-lg-4">
        <div class="db-card">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="bi bi-boxes"></i>Asset Alerts</h5>
                <a href="{{ route('admin.assets.index', ['stock' => 'low']) }}" class="btn-ghost">Stock <i class="bi bi-chevron-right" style="font-size:10px;"></i></a>
            </div>
            <div class="p-0">
                @forelse($lowStockAssets as $asset)
                    <a href="{{ route('admin.assets.index', ['search' => $asset->asset_code]) }}" class="mini-row">
                        <span class="mini-icon"><i class="bi bi-box-seam"></i></span>
                        <span class="flex-grow-1 min-w-0">
                            <span class="mini-title">{{ $asset->name }}</span>
                            <span class="mini-sub">{{ $asset->asset_code }} &middot; Min {{ $asset->minimum_quantity }}</span>
                        </span>
                        <span class="stock-warn">{{ $asset->available_quantity }} left</span>
                    </a>
                @empty
                    <div class="empty-state"><i class="bi bi-check2-circle"></i>No low stock alerts</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ── RECORDS TABLE + TOP CUSTOMERS ───────────────────────────── --}}
<div class="row g-3 db-section">
    @if($canInvoices || $canQuotations)
    <div class="col-xl-8">
        <div class="db-card">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="bi bi-table"></i>Latest Business Records</h5>
            </div>
            <div class="table-responsive">
                <table class="db-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Reference</th>
                            <th class="hide-xs">Customer</th>
                            <th>Status</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if($canInvoices)
                    @foreach($recentInvoices as $invoice)
                        <tr>
                            <td><span class="badge-type badge-invoice">Invoice</span></td>
                            <td><a href="{{ route('admin.invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a></td>
                            <td class="hide-xs">{{ $invoice->customer?->name ?? 'N/A' }}</td>
                            <td>{!! $invoice->status_badge !!}</td>
                            <td class="text-end num" style="color:var(--text-primary);font-weight:600;">{{ format_currency($invoice->total) }}</td>
                        </tr>
                    @endforeach
                    @endif
                    @if($canQuotations)
                    @foreach($recentQuotations as $quotation)
                        <tr>
                            <td><span class="badge-type badge-quotation">Quotation</span></td>
                            <td><a href="{{ route('admin.quotations.show', $quotation) }}">{{ $quotation->quotation_number }}</a></td>
                            <td class="hide-xs">{{ $quotation->customer?->name ?? 'N/A' }}</td>
                            <td>{!! $quotation->status_badge !!}</td>
                            <td class="text-end num" style="color:var(--text-primary);font-weight:600;">{{ format_currency($quotation->total) }}</td>
                        </tr>
                    @endforeach
                    @endif
                    @if((!$canInvoices || $recentInvoices->isEmpty()) && (!$canQuotations || $recentQuotations->isEmpty()))
                        <tr><td colspan="5"><div class="empty-state"><i class="bi bi-inbox"></i>No business records yet</div></td></tr>
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($canCustomers)
    <div class="col-xl-4">
        <div class="db-card">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="bi bi-trophy-fill" style="color:var(--amber)"></i>Top Customers</h5>
            </div>
            <div class="p-0">
                @forelse($topCustomers as $customer)
                    <a href="{{ route('admin.customers.show', $customer) }}" class="mini-row">
                        <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" class="customer-avatar">
                        <span class="flex-grow-1 min-w-0">
                            <span class="mini-title">{{ $customer->name }}</span>
                            <span class="mini-sub">{{ $customer->company_name ?: ($customer->mobile ?: $customer->email) }}</span>
                        </span>
                        <span class="money-pos">{{ format_currency($customer->payment_total ?? 0) }}</span>
                    </a>
                @empty
                    <div class="empty-state"><i class="bi bi-people"></i>No customer payments yet</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ── RECENT CUSTOMERS / PAYMENTS / ACTIVITY ──────────────────── --}}
<div class="row g-3 db-section">
    @if($canCustomers)
    <div class="col-lg-4">
        <div class="db-card">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="bi bi-person-plus"></i>Recent Customers</h5>
            </div>
            <div class="p-0">
                @forelse($recentCustomers as $customer)
                    <a href="{{ route('admin.customers.show', $customer) }}" class="mini-row">
                        <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" class="customer-avatar">
                        <span class="flex-grow-1 min-w-0">
                            <span class="mini-title">{{ $customer->name }}</span>
                            <span class="mini-sub">{{ $customer->company_name ?: ($customer->email ?: $customer->mobile) }}</span>
                        </span>
                        <span class="stock-warn" style="background:color-mix(in srgb, var(--sky) 12%, transparent);color:var(--sky);">{{ ucfirst($customer->status) }}</span>
                    </a>
                @empty
                    <div class="empty-state"><i class="bi bi-people"></i>No customers found</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    @if($canPayments || $canExpenses)
    <div class="col-lg-4">
        <div class="db-card">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="bi bi-cash-stack"></i>Payments &amp; Expenses</h5>
            </div>
            <div class="p-0">
                @if($canPayments)
                @foreach($recentPayments as $payment)
                    <a href="{{ route('admin.transactions.show', $payment) }}" class="mini-row">
                        <span class="mini-icon" style="color:var(--green)"><i class="bi bi-arrow-down-circle-fill"></i></span>
                        <span class="flex-grow-1 min-w-0">
                            <span class="mini-title">{{ $payment->transaction_number }}</span>
                            <span class="mini-sub">{{ $payment->customer?->name ?? 'No customer' }} &middot; {{ $payment->transaction_date?->format($dateFormat) }}</span>
                        </span>
                        <span class="money-pos">{{ format_currency($payment->amount) }}</span>
                    </a>
                @endforeach
                @endif
                @if($canExpenses)
                @foreach($recentExpenses as $expense)
                    <a href="{{ route('admin.expenses.show', $expense) }}" class="mini-row">
                        <span class="mini-icon" style="color:var(--red)"><i class="bi bi-arrow-up-circle-fill"></i></span>
                        <span class="flex-grow-1 min-w-0">
                            <span class="mini-title">{{ $expense->expense_number }}</span>
                            <span class="mini-sub">{{ $expense->vendor_name ?: ($expense->customer?->name ?: $expense->category) }}</span>
                        </span>
                        <span class="money-neg">{{ format_currency($expense->amount) }}</span>
                    </a>
                @endforeach
                @endif
                @if((!$canPayments || $recentPayments->isEmpty()) && (!$canExpenses || $recentExpenses->isEmpty()))
                    <div class="empty-state"><i class="bi bi-inbox"></i>No entries yet</div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <div class="col-lg-4">
        <div class="db-card">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="bi bi-clock-history"></i>Activity &amp; Logins</h5>
            </div>
            <div class="activity-scroll p-0">
                @foreach($recentActivities as $activity)
                    <div class="mini-row">
                        <span class="mini-icon"><i class="bi bi-activity"></i></span>
                        <span class="flex-grow-1 min-w-0">
                            <span class="mini-title">{{ $activity->description }}</span>
                            <span class="mini-sub">{{ $activity->user?->name ?? 'System' }} &middot; {{ $activity->created_at->diffForHumans() }}</span>
                        </span>
                    </div>
                @endforeach
                @foreach($recentLogins as $login)
                    <div class="mini-row">
                        <span class="mini-icon"><i class="bi bi-door-open"></i></span>
                        <span class="flex-grow-1 min-w-0">
                            <span class="mini-title">{{ $login->user?->name ?? 'Unknown user' }}</span>
                            <span class="mini-sub">{{ $login->ip_address }} &middot; {{ $login->login_at?->diffForHumans() }}</span>
                        </span>
                        {!! $login->status_badge !!}
                    </div>
                @endforeach
                @if($recentActivities->isEmpty() && $recentLogins->isEmpty())
                    <div class="empty-state"><i class="bi bi-clock"></i>No activity yet</div>
                @endif
            </div>
        </div>
    </div>

    @if($canAssets)
    <div class="col-lg-4">
        <div class="db-card">
            <div class="db-card-header">
                <h5 class="db-card-title"><i class="bi bi-arrow-left-right"></i>Assign &amp; Return History</h5>
            </div>
            <div class="p-0">
                @forelse($recentAssetMovements as $movement)
                    <a href="{{ route('admin.asset-assignments.index') }}" class="mini-row">
                        <span class="mini-icon" style="color:{{ $movement->type === 'issue' ? 'var(--sky)' : 'var(--green)' }}">
                            <i class="bi {{ $movement->type === 'issue' ? 'bi-box-arrow-right' : 'bi-box-arrow-in-left' }}"></i>
                        </span>
                        <span class="flex-grow-1 min-w-0">
                            <span class="mini-title">{{ $movement->assetItem?->name }} ({{ $movement->quantity }})</span>
                            <span class="mini-sub">{{ $movement->customer?->name ?? 'No customer' }} &middot; {{ $movement->movement_date?->format($dateFormat ?? 'd M Y') }}</span>
                        </span>
                        {!! $movement->type_badge !!}
                    </a>
                @empty
                    <div class="empty-state"><i class="bi bi-inbox"></i>No history yet</div>
                @endforelse
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const trendData       = @json($monthlyTrend);
const currencySymbol  = @json(setting('currency_symbol', '₹'));
const themeColor      = window.CRM_THEME_COLOR || '{{ $themeColor }}';

new ApexCharts(document.querySelector('#businessTrendChart'), {
    series: [
        { name: 'Invoices',    data: trendData.map(r => r.invoices) },
        { name: 'Collections', data: trendData.map(r => r.collections) },
        { name: 'Expenses',    data: trendData.map(r => r.expenses) }
    ],
    chart: {
        type: 'area',
        height: 290,
        toolbar: { show: false },
        background: 'transparent',
        fontFamily: 'DM Sans, sans-serif',
        animations: { enabled: true, easing: 'easeinout', speed: 600 }
    },
    theme: { mode: 'light' },
    colors: [themeColor, '#22c55e', '#ef4444'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2 },
    xaxis: {
        categories: trendData.map(r => r.month),
        labels: { style: { colors: '#94a3b8', fontSize: '11px' } },
        axisBorder: { show: false },
        axisTicks: { show: false }
    },
    yaxis: {
        labels: {
            style: { colors: '#94a3b8', fontSize: '11px' },
            formatter: v => new Intl.NumberFormat('en-IN', { maximumFractionDigits: 0 }).format(v)
        }
    },
    grid: {
        borderColor: 'rgba(0,0,0,.06)',
        strokeDashArray: 4,
        padding: { left: 4, right: 4 }
    },
    fill: {
        type: 'gradient',
        gradient: { shadeIntensity: 1, opacityFrom: .25, opacityTo: .02 }
    },
    tooltip: {
        theme: 'light',
        y: { formatter: v => currencySymbol + new Intl.NumberFormat('en-IN', { maximumFractionDigits: 0 }).format(v) }
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        labels: { colors: '#64748b' },
        markers: { size: 6, shape: 'circle' },
        fontSize: '12px'
    }
}).render();
</script>
@endpush