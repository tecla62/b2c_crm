@extends('layouts.app')

@section('title', $user->name)

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">User Profile</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                <li class="breadcrumb-item active">{{ $user->name }}</li>
            </ol>
        </nav>
    </div>
    @if(auth()->user()->hasPermission('users.edit'))
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
        <i class="bi bi-pencil me-2"></i>Edit User
    </a>
    @endif
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body text-center">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                <h4 class="mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-3">{{ $user->designation ?? 'No Designation' }}</p>
                <div class="mb-3">
                    {!! $user->status_badge !!}
                </div>
                <table class="table table-sm table-borderless">
                    <tr>
                        <th class="text-start">Email:</th>
                        <td class="text-end"><a href="mailto:{{ $user->email }}">{{ $user->email }}</a></td>
                    </tr>
                    <tr>
                        <th class="text-start">Mobile:</th>
                        <td class="text-end">{{ $user->mobile ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="text-start">Role:</th>
                        <td class="text-end">{!! $user->role_badge !!}</td>
                    </tr>
                    <tr>
                        <th class="text-start">Department:</th>
                        <td class="text-end">{{ $user->department ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th class="text-start">Member Since:</th>
                        <td class="text-end">{{ $user->created_at->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <th class="text-start">Last Login:</th>
                        <td class="text-end">
                            @if($user->last_login_at)
                                {{ $user->last_login_at->format('M d, Y H:i') }}
                                <br><small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                            @else
                                Never
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#loginHistory" role="tab">Login History</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#activities" role="tab">Activity Logs</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Login History Tab -->
            <div class="tab-pane fade show active" id="loginHistory" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Login Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Browser</th>
                                        <th>Device</th>
                                        <th>IP Address</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($loginHistories as $history)
                                    <tr>
                                        <td>
                                            <small>{{ $history->login_at->format('M d, Y H:i:s') }}</small>
                                        </td>
                                        <td>{{ $history->browser }}</td>
                                        <td>{{ $history->device }}</td>
                                        <td><small class="text-muted">{{ $history->ip_address }}</small></td>
                                        <td>
                                            @if($history->status === 'success')
                                                <span class="badge bg-success">Success</span>
                                            @else
                                                <span class="badge bg-danger">Failed</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No login history found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($loginHistories->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $loginHistories->links() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Activity Logs Tab -->
            <div class="tab-pane fade" id="activities" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">User Activity Logs</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Action</th>
                                        <th>Model</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($activityLogs as $log)
                                    <tr>
                                        <td>
                                            <small>{{ $log->created_at->format('M d, Y H:i:s') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ strtoupper($log->action) }}</span>
                                        </td>
                                        <td>{{ $log->model_type }}</td>
                                        <td><small class="text-muted">{{ Str::limit($log->description ?? 'N/A', 50) }}</small></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No activity logs found</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        @if($activityLogs->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $activityLogs->links() }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
