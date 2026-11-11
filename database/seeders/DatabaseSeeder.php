<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Permissions
        $permissions = [
            ['name' => 'dashboard.access',    'display_name' => 'Dashboard Access',       'module' => 'Dashboard'],
            ['name' => 'users.view',           'display_name' => 'View Users',             'module' => 'Users'],
            ['name' => 'users.create',         'display_name' => 'Create Users',           'module' => 'Users'],
            ['name' => 'users.edit',           'display_name' => 'Edit Users',             'module' => 'Users'],
            ['name' => 'users.delete',         'display_name' => 'Delete Users',           'module' => 'Users'],
            ['name' => 'roles.view',           'display_name' => 'View Roles',             'module' => 'Roles'],
            ['name' => 'roles.create',         'display_name' => 'Create Roles',           'module' => 'Roles'],
            ['name' => 'roles.edit',           'display_name' => 'Edit Roles',             'module' => 'Roles'],
            ['name' => 'roles.delete',         'display_name' => 'Delete Roles',           'module' => 'Roles'],
            ['name' => 'customers.view',       'display_name' => 'View Customers',         'module' => 'Customers'],
            ['name' => 'customers.create',     'display_name' => 'Create Customers',       'module' => 'Customers'],
            ['name' => 'customers.edit',       'display_name' => 'Edit Customers',         'module' => 'Customers'],
            ['name' => 'customers.delete',     'display_name' => 'Delete Customers',       'module' => 'Customers'],
            ['name' => 'estimations.view',     'display_name' => 'View Estimations',       'module' => 'Estimations'],
            ['name' => 'estimations.create',   'display_name' => 'Create Estimations',     'module' => 'Estimations'],
            ['name' => 'estimations.edit',     'display_name' => 'Edit Estimations',       'module' => 'Estimations'],
            ['name' => 'estimations.delete',   'display_name' => 'Delete Estimations',     'module' => 'Estimations'],
            ['name' => 'invoices.view',        'display_name' => 'View Invoices',          'module' => 'Invoices'],
            ['name' => 'invoices.create',      'display_name' => 'Create Invoices',        'module' => 'Invoices'],
            ['name' => 'invoices.edit',        'display_name' => 'Edit Invoices',          'module' => 'Invoices'],
            ['name' => 'invoices.delete',      'display_name' => 'Delete Invoices',        'module' => 'Invoices'],
            ['name' => 'transactions.view',    'display_name' => 'View Transactions',      'module' => 'Transactions'],
            ['name' => 'transactions.create',  'display_name' => 'Create Transactions',    'module' => 'Transactions'],
            ['name' => 'transactions.edit',    'display_name' => 'Edit Transactions',      'module' => 'Transactions'],
            ['name' => 'transactions.delete',  'display_name' => 'Delete Transactions',    'module' => 'Transactions'],
            ['name' => 'expenses.view',        'display_name' => 'View Expenses',          'module' => 'Expenses'],
            ['name' => 'expenses.create',      'display_name' => 'Create Expenses',        'module' => 'Expenses'],
            ['name' => 'expenses.edit',        'display_name' => 'Edit Expenses',          'module' => 'Expenses'],
            ['name' => 'expenses.delete',      'display_name' => 'Delete Expenses',        'module' => 'Expenses'],
            ['name' => 'assets.view',          'display_name' => 'View Assets',            'module' => 'Assets'],
            ['name' => 'assets.create',        'display_name' => 'Create Assets',          'module' => 'Assets'],
            ['name' => 'assets.edit',          'display_name' => 'Edit Assets',            'module' => 'Assets'],
            ['name' => 'assets.delete',        'display_name' => 'Delete Assets',          'module' => 'Assets'],
            ['name' => 'reports.view',         'display_name' => 'View Reports',           'module' => 'Reports'],
            ['name' => 'settings.view',        'display_name' => 'View Settings',          'module' => 'Settings'],
            ['name' => 'settings.edit',        'display_name' => 'Edit Settings',          'module' => 'Settings'],
            ['name' => 'activity_logs.view',   'display_name' => 'View Activity Logs',     'module' => 'Logs'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }

        // Create Roles
        $roles = [
            ['name' => 'super_admin',     'display_name' => 'Super Admin',     'color' => 'danger',  'is_system' => true,  'description' => 'Full system access'],
            ['name' => 'admin',           'display_name' => 'Admin',           'color' => 'primary', 'is_system' => true,  'description' => 'Administrative access'],
            ['name' => 'manager',         'display_name' => 'Manager',         'color' => 'info',    'is_system' => false, 'description' => 'Management access'],
            ['name' => 'sales_executive', 'display_name' => 'Sales Executive', 'color' => 'success', 'is_system' => false, 'description' => 'Sales module access'],
            ['name' => 'accountant',      'display_name' => 'Accountant',      'color' => 'warning', 'is_system' => false, 'description' => 'Finance module access'],
            ['name' => 'site_engineer',   'display_name' => 'Site Engineer',   'color' => 'secondary','is_system' => false, 'description' => 'Site operations access'],
        ];

        $createdRoles = [];
        foreach ($roles as $roleData) {
            $createdRoles[$roleData['name']] = Role::firstOrCreate(['name' => $roleData['name']], $roleData);
        }

        // Assign permissions to roles
        $allPermIds = Permission::pluck('id')->toArray();
        $createdRoles['super_admin']->syncPermissions($allPermIds);
        $createdRoles['admin']->syncPermissions(Permission::whereNotIn('name', ['settings.edit'])->pluck('id')->toArray());

        $managerPerms = Permission::whereIn('module', ['Dashboard', 'Customers', 'Estimations', 'Invoices', 'Assets', 'Reports'])->pluck('id')->toArray();
        $createdRoles['manager']->syncPermissions($managerPerms);

        $salesPerms = Permission::whereIn('module', ['Dashboard', 'Customers', 'Estimations'])->pluck('id')->toArray();
        $createdRoles['sales_executive']->syncPermissions($salesPerms);

        $accountantPerms = Permission::whereIn('module', ['Dashboard', 'Invoices', 'Transactions', 'Expenses', 'Assets', 'Reports'])->pluck('id')->toArray();
        $createdRoles['accountant']->syncPermissions($accountantPerms);

        $engineerPerms = Permission::whereIn('module', ['Dashboard', 'Customers'])->pluck('id')->toArray();
        $createdRoles['site_engineer']->syncPermissions($engineerPerms);

        // Create Super Admin User
        User::firstOrCreate(['email' => 'superadmin@waterproofcrm.com'], [
            'name'        => 'Super Admin',
            'email'       => 'superadmin@waterproofcrm.com',
            'mobile'      => '9999999999',
            'password'    => Hash::make('Admin@123'),
            'role_id'     => $createdRoles['super_admin']->id,
            'status'      => 'active',
            'designation' => 'System Administrator',
            'department'  => 'IT',
        ]);

        // Create Admin User
        User::firstOrCreate(['email' => 'admin@waterproofcrm.com'], [
            'name'        => 'Admin User',
            'email'       => 'admin@waterproofcrm.com',
            'mobile'      => '9888888888',
            'password'    => Hash::make('Admin@123'),
            'role_id'     => $createdRoles['admin']->id,
            'status'      => 'active',
            'designation' => 'Administrator',
            'department'  => 'Management',
        ]);

        // Create sample users for each role
        $sampleUsers = [
            ['name' => 'Rajesh Kumar',   'email' => 'manager@waterproofcrm.com',  'role' => 'manager',         'mobile' => '9777777777'],
            ['name' => 'Priya Sharma',   'email' => 'sales@waterproofcrm.com',    'role' => 'sales_executive', 'mobile' => '9666666666'],
            ['name' => 'Amit Patel',     'email' => 'accounts@waterproofcrm.com', 'role' => 'accountant',      'mobile' => '9555555555'],
            ['name' => 'Suresh Singh',   'email' => 'engineer@waterproofcrm.com', 'role' => 'site_engineer',   'mobile' => '9444444444'],
        ];

        foreach ($sampleUsers as $u) {
            User::firstOrCreate(['email' => $u['email']], [
                'name'     => $u['name'],
                'email'    => $u['email'],
                'mobile'   => $u['mobile'],
                'password' => Hash::make('Admin@123'),
                'role_id'  => $createdRoles[$u['role']]->id,
                'status'   => 'active',
            ]);
        }

        // Seed default settings
        $this->call(SettingsSeeder::class);
    }
}
