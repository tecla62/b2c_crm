<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(private UserService $userService) {}

    public function index(Request $request)
    {
        $query = User::with('role')->withTrashed(false);

        if ($search = $request->search) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('mobile', 'like', "%{$search}%"));
        }
        if ($status = $request->status)  $query->where('status', $status);
        if ($role   = $request->role_id) $query->where('role_id', $role);

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'email'           => 'required|email|unique:users',
            'mobile'          => 'nullable|string|max:20|unique:users',
            'role_id'         => 'nullable|exists:roles,id',
            'status'          => 'required|in:active,inactive,suspended',
            'designation'     => 'nullable|string|max:100',
            'department'      => 'nullable|string|max:100',
            'avatar'          => 'nullable|image|max:2048',
            'password_method' => 'required|in:auto,manual',
        ]);

        if ($request->password_method === 'manual') {
            $request->validate([
                'manual_password'              => 'required|min:8',
                'manual_password_confirmation' => 'required|same:manual_password',
            ]);
            $data['password'] = $request->manual_password;
        } else {
            // auto — generated on frontend, sent via hidden field
            $request->validate(['password' => 'required|min:8']);
            $data['password'] = $request->password;
        }

        // create() queues the welcome email — page redirects immediately
        $user = $this->userService->create($data, $request->password_method);

        return redirect()->route('admin.users.index')
            ->with('success', "User \"{$user->name}\" created. Welcome email is being sent to {$user->email}.");
    }

    public function show(User $user)
    {
        $user->load('role');
        $loginHistories = $user->loginHistories()->paginate(10);
        $activityLogs   = $user->activityLogs()->paginate(10);
        return view('admin.users.show', compact('user', 'loginHistories', 'activityLogs'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'email'       => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'mobile'      => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'password'    => 'nullable|min:8|confirmed',
            'role_id'     => 'nullable|exists:roles,id',
            'status'      => 'required|in:active,inactive,suspended',
            'designation' => 'nullable|string|max:100',
            'department'  => 'nullable|string|max:100',
            'avatar'      => 'nullable|image|max:2048',
        ]);

        $this->userService->update($user, $data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'User updated successfully.']);
        }
        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json(['success' => false, 'message' => 'You cannot delete your own account.'], 422);
        }
        $this->userService->delete($user);
        return response()->json(['success' => true, 'message' => 'User deleted successfully.']);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action'     => 'required|in:delete,activate,deactivate,suspend',
            'user_ids'   => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $ids   = collect($request->user_ids)->reject(fn($id) => $id == Auth::id());
        $users = User::whereIn('id', $ids);

        match ($request->action) {
            'delete'     => $users->get()->each(fn($u) => $this->userService->delete($u)),
            'activate'   => $users->update(['status' => 'active']),
            'deactivate' => $users->update(['status' => 'inactive']),
            'suspend'    => $users->update(['status' => 'suspended']),
        };

        return response()->json(['success' => true, 'message' => 'Bulk action completed successfully.']);
    }

    public function resetPassword(Request $request, User $user)
    {
        $request->validate(['password' => 'required|min:8|confirmed']);
        $this->userService->resetPassword($user, $request->password);
        return response()->json(['success' => true, 'message' => 'Password reset successfully.']);
    }

    public function forceLogout(User $user)
    {
        $this->userService->forceLogout($user);
        return response()->json(['success' => true, 'message' => 'User has been logged out from all devices.']);
    }

    public function updateStatus(Request $request, User $user)
    {
        $request->validate(['status' => 'required|in:active,inactive,suspended']);
        $this->userService->toggleStatus($user, $request->status);
        return response()->json(['success' => true, 'message' => 'User status updated.']);
    }

    public function profile()
    {
        $user           = Auth::user()->load('role');
        $loginHistories = $user->loginHistories()->paginate(10);
        return view('admin.users.profile', compact('user', 'loginHistories'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'mobile'      => ['nullable', 'string', 'max:20', Rule::unique('users')->ignore($user->id)],
            'designation' => 'nullable|string|max:100',
            'department'  => 'nullable|string|max:100',
            'address'     => 'nullable|string|max:255',
            'city'        => 'nullable|string|max:100',
            'state'       => 'nullable|string|max:100',
            'dark_mode'   => 'boolean',
            'avatar'      => 'nullable|image|max:2048',
        ]);

        $this->userService->update($user, $data);
        return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Current password is incorrect.'], 422);
        }

        $this->userService->resetPassword($user, $request->password);
        return response()->json(['success' => true, 'message' => 'Password changed successfully.']);
    }
}
