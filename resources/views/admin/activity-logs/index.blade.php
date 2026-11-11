@extends('layouts.app')

@section('title', 'Activity Logs')

@push('styles')
<style>
.al-stat{
    background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);
    padding:18px;display:flex;align-items:center;gap:14px;height:100%;
}
.al-stat-icon{
    width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;
    font-size:20px;flex-shrink:0;
}
.al-stat-label{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;}
.al-stat-value{font-size:24px;font-weight:900;color:#0f172a;line-height:1;}
.module-tabs{
    display:flex;gap:8px;overflow:auto;padding:2px 0 12px;margin-bottom:4px;
}
.module-tab{
    border:1px solid #e2e8f0;background:#fff;color:#475569;text-decoration:none;
    padding:8px 12px;border-radius:999px;font-size:12px;font-weight:800;
    display:inline-flex;align-items:center;gap:8px;white-space:nowrap;
}
.module-tab.active{background:var(--primary,#4f46e5);border-color:var(--primary,#4f46e5);color:#fff;}
.module-count{
    min-width:22px;height:22px;padding:0 7px;border-radius:999px;
    display:inline-flex;align-items:center;justify-content:center;
    background:#f1f5f9;color:#64748b;font-size:11px;font-weight:900;
}
.module-tab.active .module-count{background:rgba(255,255,255,.2);color:#fff;}
.al-user{display:flex;align-items:center;gap:10px;min-width:170px;}
.al-user img{width:34px;height:34px;border-radius:50%;object-fit:cover;}
.al-user-name{font-weight:700;color:#1e293b;line-height:1.2;}
.al-user-sub{font-size:11.5px;color:#94a3b8;}
.al-description{max-width:420px;white-space:normal;line-height:1.45;}
.al-meta{font-size:11.5px;color:#94a3b8;margin-top:3px;}
.al-ip{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12px;color:#475569;}
.analysis-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:18px;}
.analysis-title{font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.6px;color:#64748b;margin-bottom:12px;}
.break-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:9px 0;border-bottom:1px solid #f1f5f9;}
.break-row:last-child{border-bottom:0;}
.break-name{font-size:12px;font-weight:800;color:#334155;}
.break-bar{height:7px;background:#eef2ff;border-radius:999px;overflow:hidden;margin-top:4px;}
.break-fill{height:100%;background:var(--primary,#4f46e5);border-radius:999px;}
.detail-row td{background:#f8fafc!important;border-top:0!important;}
.detail-box{font-size:12px;color:#475569;line-height:1.7;}
.detail-code{background:#0f172a;color:#e2e8f0;border-radius:10px;padding:12px;white-space:pre-wrap;max-height:180px;overflow:auto;}
@media(max-width:640px){
    .al-stat{padding:14px;}
    .al-stat-value{font-size:20px;}
}
</style>
@endpush

@section('content')
@php
    $activeModule = request('module', 'all');
    $maxActionCount = max(1, $actionBreakdown->max('total') ?? 1);
    $moduleTotal = $modules->sum('total');

    $actionColor = function ($action) {
        return match (strtoupper((string) $action)) {
            'CREATE' => 'success',
            'UPDATE' => 'info',
            'DELETE' => 'danger',
            'STATUS_CHANGE' => 'warning',
            'EMAIL' => 'primary',
            'PASSWORD_RESET', 'FORCE_LOGOUT' => 'danger',
            default => 'secondary',
        };
    };
@endphp

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Activity Logs</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Activity Logs</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.login-history.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-door-open me-1"></i>Login History
    </a>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="al-stat">
            <div class="al-stat-icon" style="background:#eef2ff;color:var(--primary,#4f46e5);"><i class="bi bi-activity"></i></div>
            <div><div class="al-stat-label">Filtered Logs</div><div class="al-stat-value">{{ number_format($summary['total']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="al-stat">
            <div class="al-stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-calendar2-check"></i></div>
            <div><div class="al-stat-label">Today</div><div class="al-stat-value">{{ number_format($summary['today']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="al-stat">
            <div class="al-stat-icon" style="background:#e0f2fe;color:#0284c7;"><i class="bi bi-people"></i></div>
            <div><div class="al-stat-label">Active Users</div><div class="al-stat-value">{{ number_format($summary['users']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="al-stat">
            <div class="al-stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-exclamation-triangle"></i></div>
            <div><div class="al-stat-label">Critical</div><div class="al-stat-value">{{ number_format($summary['critical']) }}</div></div>
        </div>
    </div>
</div>

<div class="module-tabs">
    <a class="module-tab {{ $activeModule === 'all' ? 'active' : '' }}"
       href="{{ route('admin.activity-logs.index', request()->except(['module', 'page'])) }}">
        <i class="bi bi-grid"></i>All Modules <span class="module-count">{{ $moduleTotal }}</span>
    </a>
    @foreach($modules as $module)
    <a class="module-tab {{ $activeModule === $module->module ? 'active' : '' }}"
       href="{{ route('admin.activity-logs.index', array_merge(request()->except('page'), ['module' => $module->module])) }}">
        {{ $module->module }} <span class="module-count">{{ $module->total }}</span>
    </a>
    @endforeach
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-xl-8">
        <div class="card">
            <div class="table-toolbar card-header">
                <div class="d-flex flex-wrap gap-2 w-100">
                    <input type="text" id="searchInput" class="form-control" style="max-width:240px;"
                           value="{{ request('search') }}" placeholder="Search action, user, IP...">

                    <select id="actionFilter" class="form-select" style="max-width:170px;">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst(strtolower($action))) }}</option>
                        @endforeach
                    </select>

                    <select id="userFilter" class="form-select" style="max-width:220px;">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>
                            {{ $user->name }} - {{ $user->email }}
                        </option>
                        @endforeach
                    </select>

                    <input type="date" id="fromFilter" class="form-control" style="max-width:155px;" value="{{ request('from') }}">
                    <input type="date" id="toFilter" class="form-control" style="max-width:155px;" value="{{ request('to') }}">

                    <button class="btn btn-outline-primary" onclick="applyFilters()">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </a>
                </div>
            </div>

            <div class="table-responsive mobile-table-wrap">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>User</th>
                            <th>Module</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $log)
                        @php
                            $detailsId = 'activityDetails' . $log->id;
                            $subjectLabel = $log->subject_type ? class_basename($log->subject_type) . ' #' . $log->subject_id : 'N/A';
                        @endphp
                        <tr>
                            <td data-label="Date & Time">
                                <strong style="font-size:12px;">{{ $log->created_at ? $log->created_at->format('d M Y, h:i A') : 'N/A' }}</strong>
                                <div class="al-meta">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</div>
                            </td>
                            <td data-label="User">
                                <div class="al-user">
                                    <img src="{{ $log->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=S&background=6366f1&color=fff' }}" alt="">
                                    <div>
                                        <div class="al-user-name">{{ $log->user?->name ?? 'System' }}</div>
                                        <div class="al-user-sub">{{ $log->user?->email ?? 'Automated event' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Module"><span class="badge bg-light text-dark">{{ $log->module ?? 'General' }}</span></td>
                            <td data-label="Action">
                                <span class="badge bg-{{ $actionColor($log->action) }}">{{ str_replace('_', ' ', strtoupper((string) ($log->action ?? 'LOG'))) }}</span>
                            </td>
                            <td data-label="Description">
                                <div class="al-description">{{ $log->description ?? 'N/A' }}</div>
                                <div class="al-meta">Subject: {{ $subjectLabel }}</div>
                            </td>
                            <td data-label="IP Address"><span class="al-ip">{{ $log->ip_address ?? 'N/A' }}</span></td>
                            <td data-label="Details">
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#{{ $detailsId }}">
                                    <i class="bi bi-chevron-down"></i>
                                </button>
                            </td>
                        </tr>
                        <tr class="collapse detail-row" id="{{ $detailsId }}">
                            <td colspan="7">
                                <div class="detail-box">
                                    <div class="row g-3">
                                        <div class="col-md-4"><strong>Subject:</strong> {{ $subjectLabel }}</div>
                                        <div class="col-md-4"><strong>User Agent:</strong> {{ Str::limit($log->user_agent ?? 'N/A', 70) }}</div>
                                        <div class="col-md-4"><strong>Recorded:</strong> {{ $log->created_at ? $log->created_at->format('d M Y, h:i:s A') : 'N/A' }}</div>
                                        <div class="col-12">
                                            <strong>Properties</strong>
                                            <pre class="detail-code mt-2">{{ $log->properties ? json_encode($log->properties, JSON_PRETTY_PRINT) : 'No extra properties captured.' }}</pre>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>No activity logs found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <x-pagination :paginator="$activities" label="records" />
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="analysis-card mb-3">
            <div class="analysis-title">Action Analysis</div>
            @forelse($actionBreakdown as $item)
            @php($percent = round(($item->total / $maxActionCount) * 100))
            <div class="break-row">
                <div class="flex-grow-1">
                    <div class="break-name">{{ str_replace('_', ' ', strtoupper((string) ($item->action ?? 'OTHER'))) }}</div>
                    <div class="break-bar"><div class="break-fill" style="width:{{ $percent }}%;"></div></div>
                </div>
                <span class="badge bg-light text-dark">{{ $item->total }}</span>
            </div>
            @empty
            <div class="text-muted">No action data available.</div>
            @endforelse
        </div>

        <div class="analysis-card">
            <div class="analysis-title">Recent Logins</div>
            @forelse($loginHistories as $login)
            <div class="break-row">
                <div>
                    <div class="break-name">{{ $login->user?->name ?? 'Unknown User' }}</div>
                    <div class="al-meta">{{ $login->ip_address ?? 'N/A' }} - {{ $login->login_at ? $login->login_at->diffForHumans() : 'Recent' }}</div>
                </div>
                {!! $login->status_badge !!}
            </div>
            @empty
            <div class="text-muted">No login history found.</div>
            @endforelse
            <a href="{{ route('admin.login-history.index') }}" class="btn btn-outline-primary btn-sm mt-3 w-100">
                View Full Login History
            </a>
        </div>
    </div>
</div>
<div class="clearfix"></div>
@endsection

@push('scripts')
<script>
function applyFilters() {
    const params = new URLSearchParams();
    const values = {
        module: @json(request('module')),
        search: $('#searchInput').val(),
        action: $('#actionFilter').val(),
        user_id: $('#userFilter').val(),
        from: $('#fromFilter').val(),
        to: $('#toFilter').val(),
    };

    Object.entries(values).forEach(([key, value]) => {
        if (value && value !== 'all') params.set(key, value);
    });

    window.location.href = `{{ route('admin.activity-logs.index') }}?${params.toString()}`;
}

$('#searchInput').on('keypress', e => { if (e.which === 13) applyFilters(); });
$('#actionFilter,#userFilter,#fromFilter,#toFilter').on('change', applyFilters);
</script>
@endpush
