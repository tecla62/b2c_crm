@extends('layouts.app')

@section('title', 'Edit Role - ' . $role->display_name)

@section('content')
<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h1 class="page-title">Edit Role</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
                <li class="breadcrumb-item active">{{ $role->display_name }}</li>
            </ol>
        </nav>
    </div>
</div>

@include('admin.roles._form')
@endsection
