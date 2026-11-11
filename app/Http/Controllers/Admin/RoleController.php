<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->with('permissions')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        $permissions = Permission::orderBy('module')->get()->groupBy('module');
        return view('admin.roles.create', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:50|unique:roles|alpha_dash',
            'display_name' => 'required|string|max:100',
            'description'  => 'nullable|string|max:255',
            'color'        => 'required|string|max:20',
            'permissions'  => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create($data);
        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        ActivityLog::log('CREATE', 'Roles', "Created role: {$role->display_name}", $role);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Role created successfully.']);
        }
        return redirect()->route('admin.roles.index')->with('success', 'Role created successfully.');
    }

    public function edit(Role $role)
    {
        $permissions = Permission::orderBy('module')->get()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'display_name' => 'required|string|max:100',
            'description'  => 'nullable|string|max:255',
            'color'        => 'required|string|max:20',
            'permissions'  => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update($data);
        $role->syncPermissions($data['permissions'] ?? []);

        ActivityLog::log('UPDATE', 'Roles', "Updated role: {$role->display_name}", $role);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Role updated successfully.']);
        }
        return redirect()->route('admin.roles.index')->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return response()->json(['success' => false, 'message' => 'System roles cannot be deleted.'], 422);
        }
        if ($role->users()->count() > 0) {
            return response()->json(['success' => false, 'message' => 'Cannot delete role with assigned users.'], 422);
        }
        ActivityLog::log('DELETE', 'Roles', "Deleted role: {$role->display_name}", $role);
        $role->delete();
        return response()->json(['success' => true, 'message' => 'Role deleted successfully.']);
    }
}
