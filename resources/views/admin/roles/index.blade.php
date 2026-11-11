@extends('layouts.app')

@section('title', 'Roles & Permissions')

@push('styles')
<style>
.role-stat{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);padding:16px;height:100%;}
.role-label{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.5px;color:#94a3b8;margin-bottom:5px;}
.role-value{font-size:22px;font-weight:850;color:#0f172a;}
.role-icon{width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;}
.role-card{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);height:100%;overflow:hidden;border:1px solid #f1f5f9;}
.role-head{padding:18px;border-bottom:1px solid #f1f5f9;display:flex;align-items:flex-start;gap:12px;}
.role-avatar{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;flex-shrink:0;}
.role-title{font-size:16px;font-weight:850;color:#0f172a;line-height:1.2;}
.role-name{font-size:11.5px;color:#64748b;margin-top:3px;}
.role-body{padding:18px;}
.role-desc{font-size:13px;color:#64748b;min-height:38px;}
.role-metrics{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin:14px 0;}
.role-metric{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:9px;}
.role-metric strong{display:block;font-size:18px;color:#0f172a;line-height:1;}
.role-metric span{font-size:10.5px;color:#64748b;text-transform:uppercase;font-weight:800;}
.perm-preview{display:flex;flex-wrap:wrap;gap:6px;max-height:70px;overflow:hidden;}
.perm-pill{display:inline-flex;align-items:center;gap:5px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:999px;padding:4px 9px;font-size:11.5px;color:#475569;font-weight:700;}
.role-foot{padding:14px 18px;border-top:1px solid #f1f5f9;display:flex;gap:8px;align-items:center;}
.system-badge{background:#e0e7ff;color:#4338ca;}
@media(max-width:767px){.page-header .btn{width:100%;}.role-foot{flex-wrap:wrap}.role-foot .btn{flex:1;}}
</style>
@endpush

@section('content')
@php
    $totalUsers = $roles->sum('users_count');
    $systemRoles = $roles->where('is_system', true)->count();
    $totalPermissions = \App\Models\Permission::count();
@endphp

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Roles & Permissions</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Roles</li>
            </ol>
        </nav>
    </div>
    @if(auth()->user()->hasPermission('roles.create'))
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Create Role
    </a>
    @endif
</div>

<div class="row g-3 mb-3">
    @foreach([
        ['Roles', $roles->count(), 'bi-shield-check', '#e0f2fe', '#0284c7'],
        ['System Roles', $systemRoles, 'bi-lock', '#eef2ff', '#4f46e5'],
        ['Assigned Users', $totalUsers, 'bi-people', '#dcfce7', '#16a34a'],
        ['Permissions', $totalPermissions, 'bi-key', '#fef3c7', '#d97706'],
    ] as [$label,$value,$icon,$bg,$color])
    <div class="col-6 col-md-3">
        <div class="role-stat">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="role-label">{{ $label }}</div>
                    <div class="role-value">{{ number_format($value) }}</div>
                </div>
                <div class="role-icon" style="background:{{ $bg }};color:{{ $color }};"><i class="bi {{ $icon }}"></i></div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">
    @forelse($roles as $role)
    <div class="col-md-6 col-xl-4">
        <div class="role-card">
            <div class="role-head">
                <div class="role-avatar" style="background:{{ $role->color ?: '#4f46e5' }};">
                    <i class="bi {{ $role->is_system ? 'bi-shield-lock' : 'bi-person-badge' }}"></i>
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="role-title text-truncate">{{ $role->display_name }}</div>
                        @if($role->is_system)<span class="badge system-badge">System</span>@endif
                    </div>
                    <div class="role-name">{{ $role->name }}</div>
                </div>
            </div>
            <div class="role-body">
                <div class="role-desc">{{ $role->description ?: 'No description added for this role.' }}</div>
                <div class="role-metrics">
                    <div class="role-metric"><strong>{{ $role->users_count }}</strong><span>Users</span></div>
                    <div class="role-metric"><strong>{{ $role->permissions->count() }}</strong><span>Permissions</span></div>
                </div>
                <div class="perm-preview">
                    @forelse($role->permissions->take(8) as $permission)
                        <span class="perm-pill"><i class="bi bi-check2"></i>{{ $permission->display_name }}</span>
                    @empty
                        <span class="text-muted fs-13">No permissions assigned</span>
                    @endforelse
                    @if($role->permissions->count() > 8)
                        <span class="perm-pill">+{{ $role->permissions->count() - 8 }} more</span>
                    @endif
                </div>
            </div>
            <div class="role-foot">
                <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#roleModal{{ $role->id }}">
                    <i class="bi bi-eye me-1"></i>View
                </button>
                @if(auth()->user()->hasPermission('roles.edit') && $role->name !== 'super_admin')
                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
                @endif
                @if(auth()->user()->hasPermission('roles.delete') && !$role->is_system && $role->name !== 'super_admin')
                <button onclick="deleteRole({{ $role->id }})" class="btn btn-sm btn-outline-danger ms-auto">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="roleModal{{ $role->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content" style="border-radius:14px;border:none;">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title fw-800">{{ $role->display_name }}</h5>
                        <div class="text-muted fs-12">{{ $role->name }} - {{ $role->users_count }} users</div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">{{ $role->description ?: 'No description added.' }}</p>
                    @foreach($role->permissions->groupBy('module') as $module => $permissions)
                        <div class="mb-3">
                            <div class="fw-800 mb-2">{{ ucfirst(str_replace('_', ' ', $module)) }}</div>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach($permissions as $permission)
                                    <span class="perm-pill">{{ $permission->display_name }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    @if($role->permissions->isEmpty())
                        <div class="text-muted">No permissions assigned.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-shield fs-2 d-block mb-2"></i>No roles found
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
function deleteRole(id) {
    Swal.fire({
        title: 'Delete this role?',
        text: 'Users cannot keep working with a deleted role.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, delete'
    }).then(result => {
        if (!result.isConfirmed) return;
        $.ajax({
            url: '{{ route('admin.roles.destroy', '__ID__') }}'.replace('__ID__', id),
            type: 'DELETE',
            success: response => Swal.fire('Deleted', response.message, 'success').then(() => location.reload()),
            error: xhr => Swal.fire('Error', xhr.responseJSON?.message || 'Something went wrong', 'error')
        });
    });
}
</script>
@endpush
