@php
    $isEdit = isset($role);
    $selectedPermissions = collect(old('permissions', $rolePermissions ?? []))->map(fn($id) => (int) $id)->all();
    $selectedCount = count($selectedPermissions);
    $totalPermissions = $permissions->flatten()->count();
    $roleColor = old('color', $role->color ?? '#4f46e5');
@endphp

@push('styles')
<style>
.role-form-grid{display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:18px;align-items:start;}
.role-panel{background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.07);overflow:hidden;}
.role-panel-head{padding:18px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;gap:12px;}
.role-panel-title{font-size:15px;font-weight:850;color:#0f172a;margin:0;}
.role-panel-body{padding:20px;}
.color-row{display:flex;align-items:center;gap:10px;}
.color-row input[type=color]{width:46px;height:38px;border:none;background:transparent;padding:0;}
.permission-toolbar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:14px;}
.permission-search{max-width:300px;}
.permission-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;}
.permission-card{border:1px solid #e2e8f0;border-radius:12px;background:#fff;overflow:hidden;}
.permission-card-head{padding:12px 14px;background:#f8fafc;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;gap:10px;}
.permission-module{font-size:13px;font-weight:850;color:#0f172a;}
.permission-count{font-size:11px;font-weight:800;color:#64748b;}
.permission-card-body{padding:12px 14px;}
.permission-item{display:flex;align-items:flex-start;gap:9px;padding:8px;border-radius:8px;margin-bottom:3px;}
.permission-item:hover{background:#f8fafc;}
.permission-item label{font-size:13px;font-weight:650;color:#334155;cursor:pointer;line-height:1.25;}
.permission-item small{display:block;color:#94a3b8;font-size:11px;margin-top:2px;}
.role-summary{position:sticky;top:84px;}
.summary-avatar{width:58px;height:58px;border-radius:16px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:25px;margin-bottom:12px;}
.summary-stat{display:flex;justify-content:space-between;gap:10px;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13px;}
.summary-stat strong{color:#0f172a;}
@media(max-width:991px){.role-form-grid{grid-template-columns:1fr}.role-summary{position:static}.permission-grid{grid-template-columns:1fr;}}
@media(max-width:767px){.permission-toolbar .btn,.permission-search{width:100%;max-width:none}.role-panel-body{padding:16px;}}
</style>
@endpush

<form method="POST" action="{{ $isEdit ? route('admin.roles.update', $role) : route('admin.roles.store') }}" id="roleForm">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="role-form-grid">
        <div class="d-flex flex-column gap-3">
            <div class="role-panel">
                <div class="role-panel-head">
                    <h5 class="role-panel-title"><i class="bi bi-person-badge me-2"></i>Role Information</h5>
                </div>
                <div class="role-panel-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600" for="name">Role Key <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                   value="{{ old('name', $role->name ?? '') }}"
                                   placeholder="sales_manager" {{ $isEdit ? 'readonly' : 'required' }}>
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            <small class="text-muted">Lowercase letters, numbers, dash or underscore.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600" for="display_name">Display Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('display_name') is-invalid @enderror" id="display_name" name="display_name"
                                   value="{{ old('display_name', $role->display_name ?? '') }}" placeholder="Sales Manager" required>
                            @error('display_name') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Role Color <span class="text-danger">*</span></label>
                            <div class="color-row">
                                <input type="color" id="roleColor" name="color" value="{{ $roleColor }}">
                                <input type="text" class="form-control" value="{{ $roleColor }}" id="roleColorText" maxlength="20">
                            </div>
                            @error('color') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600" for="description">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="What should this role be allowed to manage?">{{ old('description', $role->description ?? '') }}</textarea>
                            @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="role-panel">
                <div class="role-panel-head">
                    <div>
                        <h5 class="role-panel-title"><i class="bi bi-key me-2"></i>Permissions</h5>
                        <div class="text-muted fs-12">Selected <span id="selectedPermissionCount">{{ $selectedCount }}</span> of {{ $totalPermissions }}</div>
                    </div>
                </div>
                <div class="role-panel-body">
                    <div class="permission-toolbar">
                        <input type="text" id="permissionSearch" class="form-control permission-search" placeholder="Search permissions...">
                        <button type="button" class="btn btn-outline-primary" onclick="setAllPermissions(true)"><i class="bi bi-check2-square me-1"></i>Select All</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="setAllPermissions(false)"><i class="bi bi-square me-1"></i>Clear All</button>
                    </div>

                    <div class="permission-grid">
                        @forelse($permissions as $module => $modulePermissions)
                            <div class="permission-card" data-module-card>
                                <div class="permission-card-head">
                                    <div>
                                        <div class="permission-module">{{ ucfirst(str_replace('_', ' ', $module)) }}</div>
                                        <div class="permission-count"><span data-module-selected>0</span>/{{ $modulePermissions->count() }} selected</div>
                                    </div>
                                    <div class="d-flex gap-1">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="setModulePermissions(this, true)">All</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="setModulePermissions(this, false)">None</button>
                                    </div>
                                </div>
                                <div class="permission-card-body">
                                    @foreach($modulePermissions as $permission)
                                        <div class="permission-item" data-permission-row>
                                            <input class="form-check-input permission-check" type="checkbox" name="permissions[]"
                                                   value="{{ $permission->id }}" id="permission{{ $permission->id }}"
                                                   @checked(in_array($permission->id, $selectedPermissions, true))>
                                            <label for="permission{{ $permission->id }}">
                                                {{ $permission->display_name }}
                                                <small>{{ $permission->name }}</small>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-warning mb-0">No permissions found.</div>
                        @endforelse
                    </div>
                    @error('permissions') <small class="text-danger d-block mt-2">{{ $message }}</small> @enderror
                </div>
            </div>
        </div>

        <div class="role-summary">
            <div class="role-panel">
                <div class="role-panel-body">
                    <div class="summary-avatar" id="summaryAvatar" style="background:{{ $roleColor }};">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-850 mb-1" id="summaryName">{{ old('display_name', $role->display_name ?? 'New Role') ?: 'New Role' }}</h5>
                    <div class="text-muted fs-12 mb-3" id="summaryKey">{{ old('name', $role->name ?? 'role_key') ?: 'role_key' }}</div>
                    <div class="summary-stat"><span>Permissions</span><strong id="summaryPermissionCount">{{ $selectedCount }}</strong></div>
                    @if($isEdit)
                        <div class="summary-stat"><span>Assigned Users</span><strong>{{ $role->users()->count() }}</strong></div>
                        <div class="summary-stat"><span>System Role</span><strong>{{ $role->is_system ? 'Yes' : 'No' }}</strong></div>
                    @endif
                    <div class="d-grid gap-2 mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>{{ $isEdit ? 'Update Role' : 'Create Role' }}
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
const checks = () => Array.from(document.querySelectorAll('.permission-check'));

function refreshPermissionCounts() {
    const selected = checks().filter(check => check.checked).length;
    document.getElementById('selectedPermissionCount').textContent = selected;
    document.getElementById('summaryPermissionCount').textContent = selected;

    document.querySelectorAll('[data-module-card]').forEach(card => {
        const moduleChecks = Array.from(card.querySelectorAll('.permission-check'));
        card.querySelector('[data-module-selected]').textContent = moduleChecks.filter(check => check.checked).length;
    });
}

function setAllPermissions(checked) {
    checks().forEach(check => check.checked = checked);
    refreshPermissionCounts();
}

function setModulePermissions(button, checked) {
    const card = button.closest('[data-module-card]');
    card.querySelectorAll('.permission-check').forEach(check => check.checked = checked);
    refreshPermissionCounts();
}

document.querySelectorAll('.permission-check').forEach(check => check.addEventListener('change', refreshPermissionCounts));

document.getElementById('permissionSearch')?.addEventListener('input', event => {
    const term = event.target.value.toLowerCase();
    document.querySelectorAll('[data-permission-row]').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
});

document.getElementById('display_name')?.addEventListener('input', event => {
    document.getElementById('summaryName').textContent = event.target.value || 'New Role';
});

document.getElementById('name')?.addEventListener('input', event => {
    document.getElementById('summaryKey').textContent = event.target.value || 'role_key';
});

document.getElementById('roleColor')?.addEventListener('input', event => {
    document.getElementById('roleColorText').value = event.target.value;
    document.getElementById('summaryAvatar').style.background = event.target.value;
});

document.getElementById('roleColorText')?.addEventListener('input', event => {
    if (/^#[0-9a-fA-F]{6}$/.test(event.target.value)) {
        document.getElementById('roleColor').value = event.target.value;
        document.getElementById('summaryAvatar').style.background = event.target.value;
    }
});

refreshPermissionCounts();
</script>
@endpush
