<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['payment', 'refund', 'expense', 'adjustment'])->default('payment');
            $table->enum('direction', ['income', 'expense'])->default('income');
            $table->date('transaction_date');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'card', 'upi', 'other'])->default('cash');
            $table->string('reference_no')->nullable();
            $table->enum('status', ['pending', 'cleared', 'failed', 'cancelled'])->default('cleared');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['transaction_date', 'status']);
            $table->index(['invoice_id', 'status']);
            $table->index(['customer_id', 'status']);
        });

        $permissions = [
            ['name' => 'transactions.view',   'display_name' => 'View Transactions',   'module' => 'Transactions'],
            ['name' => 'transactions.create', 'display_name' => 'Create Transactions', 'module' => 'Transactions'],
            ['name' => 'transactions.edit',   'display_name' => 'Edit Transactions',   'module' => 'Transactions'],
            ['name' => 'transactions.delete', 'display_name' => 'Delete Transactions', 'module' => 'Transactions'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                array_merge($permission, ['updated_at' => now(), 'created_at' => now()])
            );
        }

        $transactionPermissionIds = DB::table('permissions')
            ->whereIn('name', array_column($permissions, 'name'))
            ->pluck('id');

        $roleIds = DB::table('roles')
            ->whereIn('name', ['super_admin', 'admin', 'accountant'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($transactionPermissionIds as $permissionId) {
                DB::table('role_permissions')->updateOrInsert([
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
