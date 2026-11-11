@extends('layouts.app')

@section('title', 'Login History')

@push('styles')
<style>
.lh-stat{
    background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07);
    padding:18px;display:flex;align-items:center;gap:14px;height:100%;
}
.lh-stat-icon{
    width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;
    font-size:20px;flex-shrink:0;
}
.lh-stat-label{font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;}
.lh-stat-value{font-size:24px;font-weight:900;color:#0f172a;line-height:1;}
.lh-user{display:flex;align-items:center;gap:10px;min-width:180px;}
.lh-user img{width:34px;height:34px;border-radius:50%;object-fit:cover;}
.lh-user-name{font-weight:700;color:#1e293b;line-height:1.2;}
.lh-user-email{font-size:11.5px;color:#94a3b8;}
.lh-agent{font-size:12px;color:#64748b;line-height:1.55;}
.lh-time{font-weight:700;color:#1e293b;}
.lh-duration{font-size:11.5px;color:#94a3b8;margin-top:2px;}
.lh-ip{font-family:ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,monospace;font-size:12px;color:#475569;}
@media(max-width:640px){
    .lh-stat{padding:14px;}
    .lh-stat-value{font-size:20px;}
}
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Login History</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.activity-logs.index') }}">Activity Logs</a></li>
                <li class="breadcrumb-item active">Login History</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-activity me-1"></i>Activity Logs
    </a>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="lh-stat">
            <div class="lh-stat-icon" style="background:#eef2ff;color:var(--primary,#4f46e5);"><i class="bi bi-clock-history"></i></div>
            <div><div class="lh-stat-label">Total Records</div><div class="lh-stat-value">{{ number_format($summary['total']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="lh-stat">
            <div class="lh-stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="bi bi-check2-circle"></i></div>
            <div><div class="lh-stat-label">Successful</div><div class="lh-stat-value">{{ number_format($summary['success']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="lh-stat">
            <div class="lh-stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="bi bi-x-circle"></i></div>
            <div><div class="lh-stat-label">Failed</div><div class="lh-stat-value">{{ number_format($summary['failed']) }}</div></div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="lh-stat">
            <div class="lh-stat-icon" style="background:#fef3c7;color:#d97706;"><i class="bi bi-lock"></i></div>
            <div><div class="lh-stat-label">Locked</div><div class="lh-stat-value">{{ number_format($summary['locked']) }}</div></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-toolbar card-header">
        <div class="d-flex flex-wrap gap-2 w-100">
            <input type="text" id="searchInput" class="form-control" style="max-width:220px;"
                   value="{{ request('search') }}" placeholder="Search user, IP, browser...">

            <select id="userFilter" class="form-select" style="max-width:220px;">
                <option value="">All Users</option>
                @foreach($users as $user)
                <option value="{{ $user->id }}" {{ (string) request('user_id') === (string) $user->id ? 'selected' : '' }}>
                    {{ $user->name }} - {{ $user->email }}
                </option>
                @endforeach
            </select>

            <select id="statusFilter" class="form-select" style="max-width:150px;">
                <option value="">All Status</option>
                @foreach(['success' => 'Success', 'failed' => 'Failed', 'locked' => 'Locked'] as $value => $label)
                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>

            <select id="deviceFilter" class="form-select" style="max-width:150px;">
                <option value="">All Devices</option>
                @foreach($devices as $device)
                <option value="{{ $device }}" {{ request('device') === $device ? 'selected' : '' }}>{{ $device }}</option>
                @endforeach
            </select>

            <input type="date" id="fromFilter" class="form-control" style="max-width:155px;" value="{{ request('from') }}">
            <input type="date" id="toFilter" class="form-control" style="max-width:155px;" value="{{ request('to') }}">

            <button class="btn btn-outline-primary" onclick="applyFilters()">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            <a href="{{ route('admin.login-history.index') }}" class="btn btn-outline-secondary">
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
                    <th>Device</th>
                    <th>IP Address</th>
                    <th>Status</th>
                    <th>Logout</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @forelse($histories as $history)
                <tr>
                    <td data-label="Date & Time">
                        <div class="lh-time">{{ $history->login_at->format('d M Y, h:i A') }}</div>
                        <div class="lh-duration">{{ $history->login_at->diffForHumans() }}</div>
                    </td>
                    <td data-label="User">
                        <div class="lh-user">
                            <img src="{{ $history->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=U&background=6366f1&color=fff' }}" alt="">
                            <div>
                                <div class="lh-user-name">{{ $history->user?->name ?? 'Unknown User' }}</div>
                                <div class="lh-user-email">{{ $history->user?->email ?? 'No email' }}</div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Device">
                        <div class="lh-agent">
                            <strong>{{ $history->device ?: 'Unknown' }}</strong>
                            <div>{{ $history->browser ?: 'Unknown Browser' }} on {{ $history->platform ?: 'Unknown OS' }}</div>
                        </div>
                    </td>
                    <td data-label="IP Address"><span class="lh-ip">{{ $history->ip_address ?: 'N/A' }}</span></td>
                    <td data-label="Status">{!! $history->status_badge !!}</td>
                    <td data-label="Logout">
                        @if($history->logout_at)
                            <small>{{ $history->logout_at->format('d M Y, h:i A') }}</small>
                        @elseif($history->status === 'success')
                            <span class="badge bg-light text-dark">Active/Expired</span>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td data-label="Reason">
                        <small class="text-muted">{{ $history->failure_reason ?: '-' }}</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>No login history found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-pagination :paginator="$histories" label="records" />
</div>
@endsection

@push('scripts')
<script>
function applyFilters() {
    const params = new URLSearchParams();
    const values = {
        search: $('#searchInput').val(),
        user_id: $('#userFilter').val(),
        status: $('#statusFilter').val(),
        device: $('#deviceFilter').val(),
        from: $('#fromFilter').val(),
        to: $('#toFilter').val(),
    };

    Object.entries(values).forEach(([key, value]) => {
        if (value) params.set(key, value);
    });

    window.location.href = `{{ route('admin.login-history.index') }}?${params.toString()}`;
}

$('#searchInput').on('keypress', e => { if (e.which === 13) applyFilters(); });
$('#userFilter,#statusFilter,#deviceFilter,#fromFilter,#toFilter').on('change', applyFilters);
</script>
@endpush
