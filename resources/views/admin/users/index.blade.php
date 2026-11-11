@extends('layouts.app')

@section('title', 'User Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">User Management</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Users</li>
            </ol>
        </nav>
    </div>
    @if(auth()->user()->hasPermission('users.create'))
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add New User
    </a>
    @endif
</div>

<div class="card">
    <!-- Toolbar / Filters -->
    <div class="table-toolbar card-header">
        <div class="d-flex flex-wrap gap-2 w-100">
            <input type="text" id="searchInput" class="form-control" style="max-width:220px;" placeholder="Search users…">
            <select id="statusFilter" class="form-select" style="max-width:160px;">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
            </select>
            <select id="roleFilter" class="form-select" style="max-width:160px;">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-primary" onclick="applyFilters()">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive mobile-table-wrap">
        <table class="table table-hover" id="usersTable">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr>
                    <td data-label="Select">
                        <input type="checkbox" class="user-checkbox" value="{{ $user->id }}">
                    </td>
                    <td data-label="User">
                        <div class="user-cell">
                            <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                            <div>
                                <div class="u-name">{{ $user->name }}</div>
                                <div class="u-sub">{{ $user->designation ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Email">{{ $user->email }}</td>
                    <td data-label="Mobile">{{ $user->mobile ?? 'N/A' }}</td>
                    <td data-label="Role">
                        {!! $user->role_badge !!}
                    </td>
                    <td data-label="Status">{!! $user->status_badge !!}</td>
                    <td data-label="Last Login">
                        @if($user->last_login_at)
                            <small>{{ $user->last_login_at->diffForHumans() }}</small>
                        @else
                            <small class="text-muted">Never</small>
                        @endif
                    </td>
                    <td data-label="Actions">
                        <div class="action-btns">
                            <a href="{{ route('admin.users.show', $user) }}"
                               class="act-btn act-view" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()->hasPermission('users.edit'))
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="act-btn act-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            @if(auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id())
                            <button onclick="deleteUser({{ $user->id }})"
                                    class="act-btn act-delete" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>No users found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(auth()->user()->hasPermission('users.delete'))
    <div class="px-3 pt-3">
        <button class="btn btn-danger btn-sm" onclick="bulkAction('delete')" id="bulkDeleteBtn" style="display:none;">
            <i class="bi bi-trash me-1"></i>Delete Selected
        </button>
    </div>
    @endif
    <x-pagination :paginator="$users" label="users" />
</div>
@endsection

@push('scripts')
<script>
    function applyFilters() {
        const search = $('#searchInput').val();
        const status = $('#statusFilter').val();
        const role   = $('#roleFilter').val();
        window.location.href = `{{ route('admin.users.index') }}?search=${search}&status=${status}&role_id=${role}`;
    }

    $('#searchInput').on('keypress', function(e) { if (e.which === 13) applyFilters(); });

    $('#selectAll').change(function() {
        $('.user-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkActions();
    });
    $(document).on('change', '.user-checkbox', toggleBulkActions);

    function toggleBulkActions() {
        $('#bulkDeleteBtn').toggle($('.user-checkbox:checked').length > 0);
    }

    function deleteUser(id) {
        Swal.fire({
            title: 'Delete this user?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete!'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route('admin.users.destroy', '__ID__') }}'.replace('__ID__', id), type: 'DELETE',
                    success: r => Swal.fire('Deleted!', r.message, 'success').then(() => location.reload()),
                    error:   x => Swal.fire('Error!', x.responseJSON?.message || 'Something went wrong', 'error')
                });
            }
        });
    }

    function bulkAction(action) {
        const ids = $('.user-checkbox:checked').map(function() { return $(this).val(); }).get();
        if (!ids.length) return;
        Swal.fire({
            title: 'Are you sure?',
            text: `This will ${action} ${ids.length} user(s)`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Yes, proceed!'
        }).then(result => {
            if (result.isConfirmed) {
                $.post('{{ route('admin.users.bulk-action') }}', { action, user_ids: ids }, r =>
                    Swal.fire('Success!', r.message, 'success').then(() => location.reload())
                ).fail(x => Swal.fire('Error!', x.responseJSON?.message || 'Something went wrong', 'error'));
            }
        });
    }
</script>
@endpush
