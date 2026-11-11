@extends('layouts.app')

@section('title', 'Reports')

@push('styles')
<style>
.report-stat{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:16px;height:100%;}
.report-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-bottom:5px;}
.report-value{font-size:22px;font-weight:850;color:#0f172a;word-break:break-word;}
.report-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.report-nav{background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 2px 12px rgba(15,23,42,.06);padding:14px;margin-bottom:16px;}
.report-nav-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px;}
.report-nav-title{display:flex;align-items:center;gap:9px;font-size:13px;font-weight:900;color:#0f172a;}
.report-nav-title i{width:30px;height:30px;border-radius:8px;background:color-mix(in srgb,var(--primary,#4f46e5) 12%,#fff);color:var(--primary,#4f46e5);display:inline-flex;align-items:center;justify-content:center;}
.report-nav-count{font-size:11px;font-weight:800;color:#64748b;background:#f8fafc;border:1px solid #e2e8f0;border-radius:999px;padding:4px 9px;white-space:nowrap;}
.report-mobile-select{display:none;}
.report-tabs{display:grid;grid-template-columns:repeat(auto-fit,minmax(178px,1fr));gap:8px;}
.report-tab{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px 12px;text-decoration:none;color:#475569;font-size:12px;font-weight:850;display:flex;align-items:center;gap:9px;min-height:42px;box-shadow:none;transition:all .18s ease;}
.report-tab i{width:26px;height:26px;border-radius:8px;background:#fff;color:var(--primary,#4f46e5);display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;}
.report-tab span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.report-tab:hover{background:#fff;border-color:#cbd5e1;box-shadow:0 2px 8px rgba(15,23,42,.08);transform:translateY(-1px);}
.report-tab.active{background:var(--primary,#4f46e5);border-color:var(--primary,#4f46e5);color:#fff;box-shadow:0 6px 18px color-mix(in srgb,var(--primary,#4f46e5) 26%,transparent);}
.report-tab.active i{background:rgba(255,255,255,.18);color:#fff;}
.report-chip{display:inline-flex;align-items:center;gap:5px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:999px;padding:3px 9px;font-size:11.5px;color:#475569;font-weight:700;}
.report-sub{font-size:11.5px;color:#64748b;margin-top:2px;}
.report-money{font-weight:850;color:#0f172a;}
.report-actions{display:flex;gap:8px;flex-wrap:wrap;justify-content:flex-end;}
.advanced-filters{display:none;width:100%;padding-top:10px;border-top:1px solid #f1f5f9;margin-top:10px;flex-wrap:wrap;gap:10px;}
.advanced-filters.show{display:flex;}
.table-toolbar.card-header{padding:12px 16px;}
.table-toolbar form{gap:8px;}
.table-toolbar .form-control,.table-toolbar .form-select{height:40px;font-size:14px;}
.report-filter-search{min-width:220px;}
.report-filter-date{max-width:150px;}
.report-filter-customer{min-width:200px;}
.report-filter-status{max-width:155px;}
body.dark-mode .report-nav,body.dark-mode .report-stat{background:#1e293b;border-color:#334155;box-shadow:0 2px 12px rgba(0,0,0,.35);}
body.dark-mode .report-nav-title,body.dark-mode .report-value,body.dark-mode .report-money{color:#e2e8f0;}
body.dark-mode .report-nav-count,body.dark-mode .report-label,body.dark-mode .report-sub{color:#94a3b8;background:#0f172a;border-color:#334155;}
body.dark-mode .report-tab{background:#0f172a;border-color:#334155;color:#cbd5e1;}
body.dark-mode .report-tab i{background:#1e293b;}
body.dark-mode .report-tab:hover{background:#172036;border-color:#475569;}
body.dark-mode .report-tab.active{background:var(--primary,#4f46e5);border-color:var(--primary,#4f46e5);color:#fff;}
body.dark-mode .report-chip{background:#0f172a;border-color:#334155;color:#cbd5e1;}
@media(max-width:1200px){
  .report-actions{justify-content:stretch;}
  .report-actions .btn{flex:1;min-width:140px;}
}
@media(min-width:992px){
  #reportFilterForm{flex-wrap:wrap!important;align-items:center;gap:10px!important;}
  #reportFilterForm>.form-control,#reportFilterForm>.form-select{width:auto!important;flex:0 1 auto;}
  #reportFilterForm>.report-filter-search{flex:1 1 200px;min-width:180px;max-width:320px;}
  #reportFilterForm>.report-filter-date{flex:0 0 140px;}
  #reportFilterForm>.report-filter-customer{flex:1 1 180px;min-width:160px;max-width:260px;}
  #reportFilterForm>.report-filter-status{flex:0 0 140px;}
  #reportFilterForm>.btn{flex:0 0 auto;height:40px;white-space:nowrap;}
  #advancedFilters{flex:0 0 100%;width:100%;min-width:0;}
  #reportFilterForm.advanced-open{flex-wrap:wrap!important;}
  #reportFilterForm:has(#advancedFilters.show){flex-wrap:wrap!important;}
  #advancedFilters.show{display:grid;grid-template-columns:repeat(6,minmax(135px,1fr));align-items:center;}
  #advancedFilters .form-control,#advancedFilters .form-select{width:100%;min-width:0;}
}
@media(max-width:991px){
  .report-stat{padding:14px;}
  .report-value{font-size:19px;}
  .report-label{font-size:10px;margin-bottom:3px;}
  .report-tabs{grid-template-columns:repeat(2,minmax(0,1fr));}
}
@media(max-width:767px){
  .page-header{gap:16px;}
  .page-header .d-flex{width:100%;}
  .report-actions{width:100%;gap:6px;}
  .report-actions .btn{width:100%;font-size:13px;padding:10px 12px;}
  .report-stat{padding:12px;margin-bottom:8px;}
  .report-value{font-size:18px;}
  .report-label{font-size:9.5px;}
  .report-nav{margin:0 -2px 14px;padding:12px;border-radius:10px;}
  .report-nav-head{margin-bottom:10px;}
  .report-nav-title{font-size:12px;}
  .report-nav-count{font-size:10.5px;padding:3px 8px;}
  .report-mobile-select{display:block;margin-bottom:10px;height:42px;font-size:13px;font-weight:700;}
  .report-tabs{display:flex;gap:8px;overflow-x:auto;overflow-y:hidden;margin:0 -12px;padding:0 12px 2px;-webkit-overflow-scrolling:touch;scroll-snap-type:x proximity;}
  .report-tabs::-webkit-scrollbar{display:none;}
  .report-tab{padding:8px 11px;font-size:11px;gap:7px;min-width:max-content;min-height:38px;border-radius:999px;scroll-snap-align:start;}
  .report-tab i{width:22px;height:22px;border-radius:999px;font-size:12px;}
  .report-tab span{max-width:150px;}
  .table-toolbar form{flex-direction:column;width:100%;}
  .table-toolbar .form-control,.table-toolbar .form-select,.table-toolbar .btn{width:100%!important;max-width:none!important;height:42px;}
  .table-toolbar .btn{padding:10px 16px;font-size:13px;}
  .advanced-filters.show{flex-direction:column;}
  .advanced-filters.show .form-control,.advanced-filters.show .form-select{width:100%;}
  .table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch;}
  table.table{font-size:13px;}
  table.table td,table.table th{padding:8px 10px;}
  table.table td[data-label]::before{font-size:10px;font-weight:700;color:#64748b;}
  .pagination{margin-top:16px;}
  .pagination .page-link{padding:6px 10px;font-size:12px;}
}
@media(max-width:480px){
  .page-header{gap:12px;padding:12px 0;}
  .page-title{font-size:22px;margin-bottom:8px;}
  .breadcrumb{font-size:11px;}
  .report-stat{padding:10px;margin-bottom:6px;}
  .report-value{font-size:16px;}
  .report-label{font-size:9px;}
  .report-icon{width:32px;height:32px;font-size:16px;}
  .report-actions .btn{font-size:12px;padding:8px 10px;}
  .report-tab span{max-width:118px;}
  table.table td,table.table th{padding:6px 8px;font-size:12px;}
  table.table th{position:sticky;top:0;background:#f8fafc;z-index:10;}
  .card{margin-bottom:0;}
}
</style>
@endpush

@section('content')
@php
    $activeType = $filters['type'];
    $baseQuery = request()->except(['type', 'page']);
    $icons = [
        'sales' => 'bi-receipt',
        'quotations' => 'bi-file-earmark-text',
        'payments' => 'bi-cash-stack',
        'expenses' => 'bi-wallet2',
        'customers' => 'bi-person-lines-fill',
        'assets' => 'bi-box-seam',
        'sales-analysis' => 'bi-graph-up',
        'revenue-analysis' => 'bi-bar-chart',
        'receivables' => 'bi-exclamation-triangle',
        'payment-trends' => 'bi-arrow-up-right',
        'expense-analysis' => 'bi-pie-chart',
        'customer-performance' => 'bi-trophy',
    ];
    $statColors = ['#0284c7', '#16a34a', '#d97706', '#dc2626'];
@endphp

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Reports</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ol>
        </nav>
    </div>
    <div class="report-actions">
        <a href="{{ route('admin.reports.export.csv', request()->query()) }}" class="btn btn-outline-primary">
            <i class="bi bi-filetype-csv me-1"></i>Export CSV
        </a>
        <a href="{{ route('admin.reports.export.pdf', request()->query()) }}" class="btn btn-primary">
            <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
        </a>
    </div>
</div>

<div class="report-nav">
    <div class="report-nav-head">
        <div class="report-nav-title">
            <i class="bi bi-grid-3x3-gap"></i>
            <span>All Reports</span>
        </div>
        <span class="report-nav-count">{{ count($reportTypes) }} report tabs</span>
    </div>
    <select class="form-select report-mobile-select" id="reportTypeSelect" aria-label="Choose report">
        @foreach($reportTypes as $type => $label)
            <option value="{{ route('admin.reports.index', array_merge($baseQuery, ['type' => $type])) }}" @selected($activeType === $type)>
                {{ $label }}
            </option>
        @endforeach
    </select>
    <div class="report-tabs" aria-label="Report types">
        @foreach($reportTypes as $type => $label)
            <a href="{{ route('admin.reports.index', array_merge($baseQuery, ['type' => $type])) }}"
               class="report-tab {{ $activeType === $type ? 'active' : '' }}">
                <i class="bi {{ $icons[$type] ?? 'bi-table' }}"></i><span>{{ $label }}</span>
            </a>
        @endforeach
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach($report['summary'] as $label => $value)
        @continue(empty($label) && empty($value))
        @php $color = $statColors[$loop->index % count($statColors)]; @endphp
        <div class="col-6 col-md-3">
            <div class="report-stat">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="report-label">{{ $label ?: 'Total' }}</div>
                        <div class="report-value">{{ $value }}</div>
                    </div>
                    <div class="report-icon" style="background:{{ $color }}1a;color:{{ $color }};">
                        <i class="bi {{ $icons[$activeType] ?? 'bi-table' }}"></i>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="table-toolbar card-header">
        <form id="reportFilterForm" class="d-flex flex-wrap gap-2 w-100" method="GET" action="{{ route('admin.reports.index') }}">
            <input type="hidden" name="type" value="{{ $activeType }}">
            <input type="text" name="search" id="searchInput" class="form-control report-filter-search" 
                   placeholder="Search report..." value="{{ $filters['search'] }}">
            <input type="date" name="from" class="form-control report-filter-date" value="{{ $filters['from'] }}">
            <input type="date" name="to" class="form-control report-filter-date" value="{{ $filters['to'] }}">
            <select name="customer_id" class="form-select report-filter-customer" {{ $activeType === 'assets' ? 'disabled' : '' }}>
                <option value="">All Customers</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" @selected((string) $filters['customer_id'] === (string) $customer->id)>
                        {{ $customer->name }}{{ $customer->company_name ? ' - ' . $customer->company_name : '' }}
                    </option>
                @endforeach
            </select>
            <select name="status" class="form-select report-filter-status">
                <option value="">All Status</option>
                @foreach(['draft','sent','accepted','approved','rejected','expired','paid','partial','overdue','cancelled','pending','cleared','failed','active','inactive','blocked'] as $status)
                    <option value="{{ $status }}" @selected($filters['status'] === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </select>
            <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Filter</button>
            <button class="btn btn-outline-secondary" type="button" id="moreFiltersBtn" onclick="toggleAdvancedFilters()">
                <i class="bi bi-sliders me-1"></i><span id="moreFiltersText">More</span>
            </button>
            <a href="{{ route('admin.reports.index', ['type' => $activeType]) }}" class="btn btn-outline-secondary" title="Reset all filters">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Clear
            </a>

            <div class="advanced-filters" id="advancedFilters">
                <input type="text" name="category" class="form-control" placeholder="Category" value="{{ $filters['category'] }}">
                <select name="payment_method" class="form-select">
                    <option value="">All Methods</option>
                    @foreach(['cash','bank_transfer','cheque','card','upi','other'] as $method)
                        <option value="{{ $method }}" @selected($filters['payment_method'] === $method)>{{ ucfirst(str_replace('_', ' ', $method)) }}</option>
                    @endforeach
                </select>
                <select name="pricing_mode" class="form-select">
                    <option value="">All Pricing</option>
                    @foreach(['fixed','manual','sqft','nos'] as $mode)
                        <option value="{{ $mode }}" @selected($filters['pricing_mode'] === $mode)>{{ strtoupper($mode) }}</option>
                    @endforeach
                </select>
                <select name="condition" class="form-select">
                    <option value="">All Conditions</option>
                    @foreach(['new','good','repair','damaged'] as $condition)
                        <option value="{{ $condition }}" @selected($filters['condition'] === $condition)>{{ ucfirst($condition) }}</option>
                    @endforeach
                </select>
                <select name="stock" class="form-select">
                    <option value="">All Stock</option>
                    <option value="available" @selected($filters['stock'] === 'available')>Available</option>
                    <option value="low" @selected($filters['stock'] === 'low')>Low Stock</option>
                    <option value="out" @selected($filters['stock'] === 'out')>Not Available</option>
                </select>
                <select name="sort" class="form-select">
                    <option value="date_desc" @selected($filters['sort'] === 'date_desc')>Newest First</option>
                    <option value="date_asc" @selected($filters['sort'] === 'date_asc')>Oldest First</option>
                    <option value="amount_desc" @selected($filters['sort'] === 'amount_desc')>Amount High-Low</option>
                    <option value="amount_asc" @selected($filters['sort'] === 'amount_asc')>Amount Low-High</option>
                </select>
            </div>
        </form>
    </div>

    <div class="table-responsive mobile-table-wrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    @foreach($report['headers'] as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($report['rows'] as $i => $row)
                    <tr>
                        <td data-label="#">{{ method_exists($report['records'], 'firstItem') ? $report['records']->firstItem() + $i : $i + 1 }}</td>
                        @foreach($row['view'] as $colIndex => $value)
                            <td data-label="{{ $report['headers'][$colIndex] ?? 'Value' }}">
                                @if($colIndex === 0)
                                    <span class="fw-700">{{ $value }}</span>
                                @elseif(str_contains(strtolower($report['headers'][$colIndex] ?? ''), 'status'))
                                    <span class="report-chip">{{ $value }}</span>
                                @elseif(str_contains(strtolower($report['headers'][$colIndex] ?? ''), 'total') || str_contains(strtolower($report['headers'][$colIndex] ?? ''), 'amount') || str_contains(strtolower($report['headers'][$colIndex] ?? ''), 'paid') || str_contains(strtolower($report['headers'][$colIndex] ?? ''), 'outstanding') || str_contains(strtolower($report['headers'][$colIndex] ?? ''), 'net'))
                                    <span class="report-money">{{ $value }}</span>
                                @else
                                    <span>{{ $value ?: 'N/A' }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($report['headers']) + 1 }}" class="text-center text-muted py-5">
                            <i class="bi bi-clipboard-data fs-2 d-block mb-2"></i>No report data found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($report['records'], 'links'))
        <x-pagination :paginator="$report['records']" label="report rows" />
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleAdvancedFilters() {
    const advancedFilters = document.getElementById('advancedFilters');
    const filterForm = document.getElementById('reportFilterForm');
    const textSpan = document.getElementById('moreFiltersText');
    const isOpen = advancedFilters.classList.toggle('show');
    filterForm?.classList.toggle('advanced-open', isOpen);
    if (textSpan) {
        textSpan.textContent = isOpen ? 'Less' : 'More';
    }
}
document.getElementById('reportTypeSelect')?.addEventListener('change', e => {
    if (e.target.value) window.location.href = e.target.value;
});
document.getElementById('searchInput')?.addEventListener('keypress', e => {
    if (e.which === 13) document.getElementById('reportFilterForm').submit();
});
if (['category','payment_method','pricing_mode','condition','stock'].some(key => new URLSearchParams(location.search).get(key))) {
    document.getElementById('advancedFilters')?.classList.add('show');
    document.getElementById('reportFilterForm')?.classList.add('advanced-open');
}
</script>
@endpush
