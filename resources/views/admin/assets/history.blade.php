@extends('layouts.app')
@section('title', 'Asset Movement History')

@push('styles')
<style>
.asset-chip { display:inline-flex;align-items:center;gap:5px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:999px;padding:3px 9px;font-size:11.5px;color:#475569;font-weight:700; }
.movement-info { font-size: 13px; color: #475569; }
.movement-info strong { color: #0f172a; }
.movement-sub { font-size: 11.5px; color: #64748b; margin-top: 2px; }
.page-header .btn { white-space: nowrap; }
</style>
@endpush

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Asset Movement History</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.assets.index') }}">Assets</a></li>
                <li class="breadcrumb-item active">History</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.asset-assignments.index') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left-right me-1"></i>Assignments
        </a>
    </div>
</div>

<div class="card">
    <div class="table-toolbar card-header">
        <form method="GET" action="{{ route('admin.assets.history') }}" class="d-flex flex-wrap gap-2 w-100">
            <input type="text" name="search" class="form-control" style="max-width:250px;" placeholder="Search asset / customer / ID" value="{{ request('search') }}">
            
            <select name="type" class="form-select" style="max-width:180px;">
                <option value="">All Movement Types</option>
                <option value="issue" {{ request('type') === 'issue' ? 'selected' : '' }}>Issue</option>
                <option value="return" {{ request('type') === 'return' ? 'selected' : '' }}>Return</option>
                <option value="stock_in" {{ request('type') === 'stock_in' ? 'selected' : '' }}>Stock In</option>
                <option value="damaged" {{ request('type') === 'damaged' ? 'selected' : '' }}>Damaged</option>
                <option value="maintenance" {{ request('type') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="maintenance_return" {{ request('type') === 'maintenance_return' ? 'selected' : '' }}>Back From Maintenance</option>
                <option value="lost" {{ request('type') === 'lost' ? 'selected' : '' }}>Lost</option>
            </select>
            
            <select name="asset_item_id" class="form-select" style="max-width:200px;">
                <option value="">All Assets</option>
                @foreach($assets as $asset)
                <option value="{{ $asset->id }}" {{ request('asset_item_id') == $asset->id ? 'selected' : '' }}>{{ $asset->name }} ({{ $asset->asset_code }})</option>
                @endforeach
            </select>
            
            <button type="submit" class="btn btn-primary"><i class="bi bi-funnel me-1"></i>Filter</button>
            @if(request()->hasAny(['search','type','asset_item_id']))
            <a href="{{ route('admin.assets.history') }}" class="btn btn-outline-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
            @endif
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Asset Details</th>
                    <th>Movement Type</th>
                    <th>Quantity</th>
                    <th>Customer / Employee</th>
                    <th>Notes &amp; Reference</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                <tr>
                    <td data-label="Date">
                        <div class="fw-600">{{ $movement->movement_date?->format('d M Y') }}</div>
                        <div class="text-muted" style="font-size: 11px;">{{ $movement->created_at?->format('h:i A') }}</div>
                    </td>
                    <td data-label="Asset Details">
                        <div class="fw-800">{{ $movement->assetItem?->name ?? 'N/A' }}</div>
                        <div class="movement-sub">{{ $movement->assetItem?->asset_code }}</div>
                    </td>
                    <td data-label="Movement Type">
                        {!! $movement->type_badge !!}
                    </td>
                    <td data-label="Quantity">
                        <div class="fw-bold" style="font-size: 16px;">{{ $movement->quantity }}</div>
                    </td>
                    <td data-label="Customer / Employee">
                        @if($movement->customer)
                            <div class="movement-info"><i class="bi bi-building text-muted me-1"></i> <strong>{{ $movement->customer->name }}</strong></div>
                            @if($movement->customer->company_name)
                            <div class="movement-sub">{{ $movement->customer->company_name }}</div>
                            @endif
                        @endif
                        @if($movement->employee)
                            <div class="movement-info mt-1"><i class="bi bi-person-badge text-muted me-1"></i> {{ $movement->employee->name }}</div>
                        @endif
                        @if(!$movement->customer && !$movement->employee)
                            <span class="text-muted" style="font-size: 12px;">N/A</span>
                        @endif
                    </td>
                    <td data-label="Notes & Reference">
                        @if($movement->reference_no)
                            <div><span class="asset-chip"><i class="bi bi-hash"></i>{{ $movement->reference_no }}</span></div>
                        @endif
                        @if($movement->notes)
                            <div class="mt-1" style="font-size: 12px; color: #64748b; max-width: 250px;">{{ Str::limit($movement->notes, 60) }}</div>
                        @endif
                        @if(!$movement->reference_no && !$movement->notes)
                            <span class="text-muted" style="font-size: 12px;">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted py-5"><i class="bi bi-clock-history fs-2 d-block mb-2"></i>No movement history found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$movements" label="movements" />
</div>
@endsection
