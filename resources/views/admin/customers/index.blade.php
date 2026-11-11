@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Customers</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Customers</li>
            </ol>
        </nav>
    </div>
    @if(auth()->user()->hasPermission('customers.create'))
    <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-2"></i>Add Customer
    </a>
    @endif
</div>

<div class="card">
    <!-- Toolbar -->
    <div class="table-toolbar card-header">
        <div class="d-flex flex-wrap gap-2 w-100">
            <input type="text" id="searchInput" class="form-control" style="max-width:220px;" placeholder="Search customers…" value="{{ request('search') }}">
            <select id="statusFilter" class="form-select" style="max-width:150px;">
                <option value="">All Status</option>
                <option value="active"   {{ request('status') == 'active'   ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="blocked"  {{ request('status') == 'blocked'  ? 'selected' : '' }}>Blocked</option>
            </select>
            <select id="typeFilter" class="form-select" style="max-width:150px;">
                <option value="">All Types</option>
                <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>Individual</option>
                <option value="company"    {{ request('type') == 'company'    ? 'selected' : '' }}>Company</option>
            </select>
            <select id="sourceFilter" class="form-select" style="max-width:150px;">
                <option value="">All Sources</option>
                @foreach(['referral','website','walk-in','social media','advertisement','other'] as $src)
                <option value="{{ $src }}" {{ request('source') == $src ? 'selected' : '' }}>{{ ucfirst($src) }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-primary" onclick="applyFilters()">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            @if(request()->hasAny(['search','status','type','source']))
            <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Clear
            </a>
            @endif
        </div>
    </div>

    <!-- Table -->
    <div class="table-responsive mobile-table-wrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Company</th>
                    <th>Type</th>
                    <th>Source</th>
                    <th>Assigned To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                <tr>
                    <td data-label="Select">
                        <input type="checkbox" class="customer-checkbox" value="{{ $customer->id }}">
                    </td>
                    <td data-label="Customer">
                        <div class="user-cell">
                            <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}">
                            <div>
                                <div class="u-name">{{ $customer->name }}</div>
                                <div class="u-sub">{{ $customer->city ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>
                    <td data-label="Contact">
                        <div>{{ $customer->email ?? '—' }}</div>
                        <small class="text-muted">{{ $customer->mobile ?? '—' }}</small>
                    </td>
                    <td data-label="Company">{{ $customer->company_name ?? '—' }}</td>
                    <td data-label="Type">{!! $customer->type_badge !!}</td>
                    <td data-label="Source">
                        @if($customer->source)
                            <span class="badge bg-light text-dark">{{ ucfirst($customer->source) }}</span>
                        @else —
                        @endif
                    </td>
                    <td data-label="Assigned To">
                        {{ $customer->assignedTo?->name ?? '—' }}
                    </td>
                    <td data-label="Status">{!! $customer->status_badge !!}</td>
                    <td data-label="Actions">
                        <div class="action-btns">
                            <a href="{{ route('admin.customers.show', $customer) }}"
                               class="act-btn act-view" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if(auth()->user()->hasPermission('customers.edit'))
                            <a href="{{ route('admin.customers.edit', $customer) }}"
                               class="act-btn act-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endif
                            @if(auth()->user()->hasPermission('customers.delete'))
                            <button onclick="deleteCustomer({{ $customer->id }})"
                                    class="act-btn act-delete" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-4 d-block mb-2"></i>No customers found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Bulk + Pagination -->
    @if(auth()->user()->hasPermission('customers.delete'))
    <div class="px-3 pt-3">
        <button class="btn btn-danger btn-sm" onclick="bulkAction('delete')" id="bulkDeleteBtn" style="display:none;">
            <i class="bi bi-trash me-1"></i>Delete Selected
        </button>
    </div>
    @endif
    <x-pagination :paginator="$customers" label="customers" />
</div>
@endsection

@push('scripts')
<script>
function applyFilters() {
    const search = $('#searchInput').val();
    const status = $('#statusFilter').val();
    const type   = $('#typeFilter').val();
    const source = $('#sourceFilter').val();
    window.location.href = `{{ route('admin.customers.index') }}?search=${search}&status=${status}&type=${type}&source=${source}`;
}
$('#searchInput').on('keypress', e => { if (e.which === 13) applyFilters(); });

$('#selectAll').change(function() {
    $('.customer-checkbox').prop('checked', $(this).prop('checked'));
    toggleBulk();
});
$(document).on('change', '.customer-checkbox', toggleBulk);
function toggleBulk() {
    $('#bulkDeleteBtn').toggle($('.customer-checkbox:checked').length > 0);
}

function deleteCustomer(id) {
    Swal.fire({
        title: 'Delete this customer?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete!'
    }).then(result => {
        if (result.isConfirmed) {
            $.ajax({
                url: `{{ route('admin.customers.destroy', ':id') }}`.replace(':id', id), type: 'DELETE',
                success: r => Swal.fire('Deleted!', r.message, 'success').then(() => location.reload()),
                error:   x => Swal.fire('Error!', x.responseJSON?.message || 'Something went wrong', 'error')
            });
        }
    });
}

function bulkAction(action) {
    const ids = $('.customer-checkbox:checked').map(function() { return $(this).val(); }).get();
    if (!ids.length) return;
    Swal.fire({
        title: 'Are you sure?',
        text: `This will ${action} ${ids.length} customer(s)`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Yes, proceed!'
    }).then(result => {
        if (result.isConfirmed) {
            $.post('{{ route('admin.customers.bulk-action') }}', { action, customer_ids: ids }, r =>
                Swal.fire('Success!', r.message, 'success').then(() => location.reload())
            ).fail(x => Swal.fire('Error!', x.responseJSON?.message || 'Something went wrong', 'error'));
        }
    });
}
</script>
@endpush
