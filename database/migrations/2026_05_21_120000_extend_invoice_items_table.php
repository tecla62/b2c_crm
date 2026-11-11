<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('invoice_id')->constrained('services')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('service_id')->constrained('service_categories')->nullOnDelete();
            $table->integer('sl_no')->default(1)->after('sort_order');
            $table->integer('option_no')->default(1)->after('sl_no');
            $table->enum('area_mode', ['manual','sqft','nos'])->default('manual')->after('option_no');
            $table->decimal('area_value', 10, 2)->nullable()->after('area_mode');
            $table->decimal('rate_per_sft', 12, 2)->nullable()->after('area_value');
            $table->decimal('area_nos', 10, 2)->nullable()->after('rate_per_sft');
            $table->decimal('rate_per_nos', 12, 2)->nullable()->after('area_nos');

        });
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['service_id','category_id','sl_no','option_no','area_mode','area_value','rate_per_sft','area_nos','rate_per_nos']);
        });
    }
};
