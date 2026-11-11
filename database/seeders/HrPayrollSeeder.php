<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class HrPayrollSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'employees.view',     'display_name' => 'View Employees',      'module' => 'Staff & HR'],
            ['name' => 'employees.create',   'display_name' => 'Create Employees',    'module' => 'Staff & HR'],
            ['name' => 'employees.edit',     'display_name' => 'Edit Employees',      'module' => 'Staff & HR'],
            ['name' => 'employees.delete',   'display_name' => 'Delete Employees',    'module' => 'Staff & HR'],

            ['name' => 'attendance.view',    'display_name' => 'View Attendance',     'module' => 'Attendance'],
            ['name' => 'attendance.create',  'display_name' => 'Record Attendance',   'module' => 'Attendance'],
            ['name' => 'attendance.edit',    'display_name' => 'Edit Attendance',     'module' => 'Attendance'],
            ['name' => 'attendance.delete',  'display_name' => 'Delete Attendance',   'module' => 'Attendance'],

            ['name' => 'payroll.view',       'display_name' => 'View Payroll',        'module' => 'Payroll'],
            ['name' => 'payroll.create',     'display_name' => 'Process Payroll',     'module' => 'Payroll'],
            ['name' => 'payroll.edit',       'display_name' => 'Edit / Pay Payroll',  'module' => 'Payroll'],
            ['name' => 'payroll.delete',     'display_name' => 'Delete Payroll',      'module' => 'Payroll'],
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm['name']], $perm);
        }

        $superAdmin = Role::where('name', 'super_admin')->first();
        if ($superAdmin) {
            $allPerms = Permission::pluck('id')->toArray();
            $superAdmin->syncPermissions($allPerms);
        }

        $admin = Role::where('name', 'admin')->first();
        if ($admin) {
            $adminPerms = Permission::whereNotIn('name', ['settings.edit'])->pluck('id')->toArray();
            $admin->syncPermissions($adminPerms);
        }

        $manager = Role::where('name', 'manager')->first();
        if ($manager) {
            $mgrPerms = Permission::whereIn('module', ['Dashboard', 'Customers', 'Estimations', 'Invoices', 'Assets', 'Reports', 'Staff & HR', 'Attendance', 'Payroll'])->pluck('id')->toArray();
            $manager->syncPermissions($mgrPerms);
        }

        $accountant = Role::where('name', 'accountant')->first();
        if ($accountant) {
            $accPerms = Permission::whereIn('module', ['Dashboard', 'Invoices', 'Transactions', 'Expenses', 'Assets', 'Reports', 'Staff & HR', 'Attendance', 'Payroll'])->pluck('id')->toArray();
            $accountant->syncPermissions($accPerms);
        }

        $engineer = Role::where('name', 'site_engineer')->first();
        if ($engineer) {
            $engPerms = Permission::whereIn('name', ['dashboard.access', 'customers.view', 'attendance.view', 'attendance.create'])->pluck('id')->toArray();
            $engineer->syncPermissions($engPerms);
        }
    }
}
