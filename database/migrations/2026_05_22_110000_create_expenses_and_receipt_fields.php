<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->timestamp('receipt_sent_at')->nullable()->after('notes');
            $table->string('receipt_sent_to')->nullable()->after('receipt_sent_at');
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number')->unique();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->date('expense_date');
            $table->string('category')->default('materials');
            $table->string('vendor_name')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'bank_transfer', 'cheque', 'card', 'upi', 'other'])->default('cash');
            $table->string('reference_no')->nullable();
            $table->enum('status', ['pending', 'approved', 'paid', 'cancelled'])->default('paid');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['invoice_id', 'status']);
            $table->index(['transaction_id', 'status']);
            $table->index(['expense_date', 'status']);
        });

        $permissions = [
            ['name' => 'expenses.view',   'display_name' => 'View Expenses',   'module' => 'Expenses'],
            ['name' => 'expenses.create', 'display_name' => 'Create Expenses', 'module' => 'Expenses'],
            ['name' => 'expenses.edit',   'display_name' => 'Edit Expenses',   'module' => 'Expenses'],
            ['name' => 'expenses.delete', 'display_name' => 'Delete Expenses', 'module' => 'Expenses'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $permission['name']],
                array_merge($permission, ['updated_at' => now(), 'created_at' => now()])
            );
        }

        $permissionIds = DB::table('permissions')
            ->whereIn('name', array_column($permissions, 'name'))
            ->pluck('id');

        $roleIds = DB::table('roles')
            ->whereIn('name', ['super_admin', 'admin', 'accountant'])
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                DB::table('role_permissions')->updateOrInsert([
                    'role_id'       => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['receipt_sent_at', 'receipt_sent_to']);
        });
    }
};
