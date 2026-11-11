<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Extend asset_employees table with salary and work fields
        Schema::table('asset_employees', function (Blueprint $table) {
            $table->enum('salary_type', ['weekly', 'monthly', 'daily_ondemand'])->default('weekly')->after('designation');
            $table->decimal('base_salary', 12, 2)->default(0.00)->after('salary_type');
            $table->decimal('extra_duty_rate', 12, 2)->default(0.00)->after('base_salary');
            $table->decimal('daily_working_hours', 4, 2)->default(8.00)->after('extra_duty_rate');
            $table->date('joining_date')->nullable()->after('daily_working_hours');
            $table->text('address')->nullable()->after('joining_date');
            $table->string('bank_name')->nullable()->after('address');
            $table->string('account_number')->nullable()->after('bank_name');
            $table->string('ifsc_code')->nullable()->after('account_number');
        });

        // 2. Create attendances table for daily site work & punch-in/punch-out & extra duty
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('asset_employees')->cascadeOnDelete();
            $table->date('date')->index();
            $table->string('site_name')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->time('punch_in')->nullable();
            $table->time('punch_out')->nullable();
            $table->enum('status', ['present', 'half_day', 'absent', 'holiday', 'leave'])->default('present');
            $table->decimal('regular_hours', 5, 2)->default(8.00);
            $table->boolean('has_extra_duty')->default(false);
            $table->decimal('extra_duty_hours', 5, 2)->default(0.00);
            $table->decimal('extra_duty_rate', 12, 2)->default(0.00);
            $table->string('extra_duty_job')->nullable();
            $table->decimal('extra_duty_amount', 12, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Create payrolls table for weekly/monthly salary calculations
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('payroll_number')->unique();
            $table->foreignId('employee_id')->constrained('asset_employees')->cascadeOnDelete();
            $table->enum('period_type', ['weekly', 'monthly', 'daily_ondemand'])->default('weekly');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_present', 5, 2)->default(0.00);
            $table->decimal('days_half_day', 5, 2)->default(0.00);
            $table->decimal('days_absent', 5, 2)->default(0.00);
            $table->decimal('total_working_days', 5, 2)->default(0.00);
            $table->decimal('base_salary_rate', 12, 2)->default(0.00);
            $table->decimal('base_salary_earned', 12, 2)->default(0.00);
            $table->decimal('total_extra_duty_hours', 6, 2)->default(0.00);
            $table->decimal('total_extra_duty_amount', 12, 2)->default(0.00);
            $table->decimal('allowance_amount', 12, 2)->default(0.00);
            $table->string('allowance_notes')->nullable();
            $table->decimal('deduction_amount', 12, 2)->default(0.00);
            $table->string('deduction_notes')->nullable();
            $table->decimal('gross_amount', 12, 2)->default(0.00);
            $table->decimal('net_amount', 12, 2)->default(0.00);
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->string('payment_method')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Create payroll_items table for itemized daily site work & extra duty snapshot
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained('payrolls')->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained('attendances')->nullOnDelete();
            $table->date('date');
            $table->string('site_name')->nullable();
            $table->string('punch_in')->nullable();
            $table->string('punch_out')->nullable();
            $table->string('status')->default('present');
            $table->decimal('regular_hours', 5, 2)->default(8.00);
            $table->decimal('extra_duty_hours', 5, 2)->default(0.00);
            $table->decimal('extra_duty_rate', 12, 2)->default(0.00);
            $table->string('extra_duty_job')->nullable();
            $table->decimal('extra_duty_amount', 12, 2)->default(0.00);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payrolls');
        Schema::dropIfExists('attendances');
        Schema::table('asset_employees', function (Blueprint $table) {
            $table->dropColumn([
                'salary_type', 'base_salary', 'extra_duty_rate', 'daily_working_hours',
                'joining_date', 'address', 'bank_name', 'account_number', 'ifsc_code'
            ]);
        });
    }
};
