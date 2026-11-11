<?php

namespace App\Services;

use App\Mail\WelcomeUserMail;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class UserService
{
    public function create(array $data, string $method = 'auto'): User
    {
        $plainPassword = $data['password'];

        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            $data['avatar'] = $this->uploadAvatar($data['avatar']);
        }

        unset($data['password_method']);
        $data['password'] = Hash::make($plainPassword);

        $user = User::create($data);
        ActivityLog::log('CREATE', 'Users', "Created user: {$user->name}", $user);

        // Queue the welcome email — dispatched to background, page redirects immediately
        Mail::to($user->email)
            ->send(new WelcomeUserMail($user->load('role'), $plainPassword));

        return $user;
    }

    public function update(User $user, array $data): User
    {
        if (isset($data['avatar']) && $data['avatar'] instanceof UploadedFile) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
                if (!is_link(public_path('storage'))) {
                    @unlink(public_path('storage/' . $user->avatar));
                }
            }
            $data['avatar'] = $this->uploadAvatar($data['avatar']);
        }
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        ActivityLog::log('UPDATE', 'Users', "Updated user: {$user->name}", $user);
        return $user;
    }

    public function delete(User $user): void
    {
        ActivityLog::log('DELETE', 'Users', "Deleted user: {$user->name}", $user);
        $user->delete();
    }

    public function toggleStatus(User $user, string $status): User
    {
        $user->update(['status' => $status]);
        ActivityLog::log('STATUS_CHANGE', 'Users', "Changed user {$user->name} status to {$status}", $user);
        return $user;
    }

    public function resetPassword(User $user, string $password): void
    {
        $user->update(['password' => Hash::make($password)]);
        ActivityLog::log('PASSWORD_RESET', 'Users', "Reset password for user: {$user->name}", $user);
    }

    public function forceLogout(User $user): void
    {
        $user->update(['remember_token' => null]);
        ActivityLog::log('FORCE_LOGOUT', 'Users', "Force logged out user: {$user->name}", $user);
    }

    private function uploadAvatar(UploadedFile $file): string
    {
        $path = $file->store('avatars', 'public');

        // Ensure file is accessible from public/storage even if symlink is not supported
        $publicDest = public_path('storage/' . $path);
        if (!is_link(public_path('storage')) && !file_exists($publicDest)) {
            @mkdir(dirname($publicDest), 0755, true);
            @copy(storage_path('app/public/' . $path), $publicDest);
        }

        return $path;
    }
}
