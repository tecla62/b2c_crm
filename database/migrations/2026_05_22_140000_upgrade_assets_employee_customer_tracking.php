<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->nullable()->unique();
            $table->string('name');
            $table->string('mobile')->nullable();
            $table->string('designation')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['status', 'name']);
        });

        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_item_id')->constrained('asset_items')->cascadeOnDelete();
            $table->foreignId('asset_employee_id')->constrained('asset_employees')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('returned_quantity')->default(0);
            $table->date('issue_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['issued', 'partial_returned', 'returned'])->default('issued');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'issue_date']);
        });

        Schema::table('asset_movements', function (Blueprint $table) {
            $table->foreignId('asset_employee_id')->nullable()->after('movement_date')->constrained('asset_employees')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->after('asset_employee_id')->constrained('customers')->nullOnDelete();
            $table->foreignId('asset_assignment_id')->nullable()->after('customer_id')->constrained('asset_assignments')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asset_movements', function (Blueprint $table) {
            $table->dropConstrainedForeignId('asset_assignment_id');
            $table->dropConstrainedForeignId('customer_id');
            $table->dropConstrainedForeignId('asset_employee_id');
        });

        Schema::dropIfExists('asset_assignments');
        Schema::dropIfExists('asset_employees');
    }
};
