<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NotificationCenter
{
    public static function activity(string $action, string $module, string $description, $subject = null, ?int $actorId = null): void
    {
        if (setting('notif_browser', '1') !== '1') {
            return;
        }

        $module = trim($module);
        if (! self::shouldNotify($action, $module)) {
            return;
        }

        $users = self::recipients($module, $actorId);
        if ($users->isEmpty()) {
            return;
        }

        $url = self::subjectUrl($module, $subject);
        $now = now();
        $rows = $users->map(fn (User $user) => [
            'id' => (string) Str::uuid(),
            'type' => 'crm.activity',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'data' => json_encode([
                'title' => self::title($action, $module),
                'message' => $description,
                'module' => $module,
                'action' => $action,
                'icon' => self::icon($module, $action),
                'url' => $url,
            ]),
            'read_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::table('notifications')->insert($rows);
    }

    private static function shouldNotify(string $action, string $module): bool
    {
        $allowedModules = [
            'Customers', 'Services', 'Quotations', 'Invoices',
            'Transactions', 'Expenses', 'Assets', 'Users',
        ];
        $allowedActions = [
            'CREATE', 'UPDATE', 'DELETE', 'STATUS_CHANGE', 'SEND', 'EMAIL',
        ];

        return in_array($module, $allowedModules, true)
            && in_array($action, $allowedActions, true);
    }

    private static function recipients(string $module, ?int $actorId)
    {
        $permission = self::permissionForModule($module);

        $users = User::with('role.permissions', 'userPermissions')
            ->where('status', 'active')
            ->get()
            ->filter(fn (User $user) => ! $permission || $user->hasPermission($permission));

        $withoutActor = $users->reject(fn (User $user) => $actorId && $user->id === $actorId);

        return $withoutActor->isNotEmpty() ? $withoutActor->values() : $users->values();
    }

    private static function permissionForModule(string $module): ?string
    {
        return match ($module) {
            'Customers' => 'customers.view',
            'Services' => 'services.view',
            'Quotations' => 'quotations.view',
            'Invoices' => 'invoices.view',
            'Transactions' => 'transactions.view',
            'Expenses' => 'expenses.view',
            'Assets' => 'assets.view',
            'Users' => 'users.view',
            default => null,
        };
    }

    private static function subjectUrl(string $module, $subject): ?string
    {
        if (! $subject || ! isset($subject->id)) {
            return null;
        }

        return match ($module) {
            'Customers' => route('admin.customers.show', $subject, false),
            'Quotations' => route('admin.quotations.show', $subject, false),
            'Invoices' => route('admin.invoices.show', $subject, false),
            'Transactions' => route('admin.transactions.show', $subject, false),
            'Expenses' => route('admin.expenses.show', $subject, false),
            'Services' => route('admin.services.index', [], false),
            'Assets' => route('admin.assets.index', [], false),
            'Users' => route('admin.users.show', $subject, false),
            default => null,
        };
    }

    private static function title(string $action, string $module): string
    {
        $label = strtolower(str_replace('_', ' ', $action));

        return "{$module} {$label}";
    }

    private static function icon(string $module, string $action): string
    {
        if ($action === 'DELETE') {
            return 'bi-trash';
        }

        return match ($module) {
            'Customers' => 'bi-person-badge',
            'Services' => 'bi-grid',
            'Quotations' => 'bi-file-earmark-text',
            'Invoices' => 'bi-receipt',
            'Transactions' => 'bi-cash-stack',
            'Expenses' => 'bi-wallet2',
            'Assets' => 'bi-box-seam',
            'Users' => 'bi-people',
            default => 'bi-bell',
        };
    }
}
