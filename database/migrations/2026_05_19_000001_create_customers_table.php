<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('mobile', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('avatar')->nullable();

            // Company Info
            $table->string('company_name')->nullable();
            $table->string('gst_number', 20)->nullable();
            $table->string('website')->nullable();

            // Address
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable()->default('India');
            $table->string('pincode', 10)->nullable();

            // Business
            $table->string('source')->nullable();   // referral, website, walk-in, etc.
            $table->string('type')->default('individual'); // individual, company
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('active');
            $table->text('notes')->nullable();

            // Assigned to
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
