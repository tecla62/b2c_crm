<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_items', function (Blueprint $table) {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->string('unit', 30)->default('pcs');
            $table->string('location')->nullable();
            $table->unsignedInteger('total_quantity')->default(0);
            $table->unsignedInteger('issued_quantity')->default(0);
            $table->unsignedInteger('damaged_quantity')->default(0);
            $table->unsignedInteger('maintenance_quantity')->default(0);
            $table->unsignedInteger('lost_quantity')->default(0);
            $table->unsignedInteger('minimum_quantity')->default(0);
            $table->enum('condition', ['new', 'good', 'repair', 'damaged'])->default('good');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'category']);
        });

        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_item_id')->constrained('asset_items')->cascadeOnDelete();
            $table->enum('type', ['stock_in', 'issue', 'return', 'damaged', 'maintenance', 'maintenance_return', 'lost']);
            $table->unsignedInteger('quantity');
            $table->date('movement_date');
            $table->string('employee_name')->nullable();
            $table->string('site_name')->nullable();
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['type', 'movement_date']);
        });

        $permissions = [
            ['name' => 'assets.view',   'display_name' => 'View Assets',   'module' => 'Assets'],
            ['name' => 'assets.create', 'display_name' => 'Create Assets', 'module' => 'Assets'],
            ['name' => 'assets.edit',   'display_name' => 'Edit Assets',   'module' => 'Assets'],
            ['name' => 'assets.delete', 'display_name' => 'Delete Assets', 'module' => 'Assets'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(['name' => $permission['name']], $permission);
        }

        $assetPermissionIds = DB::table('permissions')->where('module', 'Assets')->pluck('id');
        $roleIds = DB::table('roles')->whereIn('name', ['super_admin', 'admin', 'accountant', 'manager'])->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($assetPermissionIds as $permissionId) {
                DB::table('role_permissions')->updateOrInsert([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
        Schema::dropIfExists('asset_items');
        DB::table('permissions')->where('module', 'Assets')->delete();
    }
};
