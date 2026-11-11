<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->unsignedTinyInteger('sl_no')->default(1)->after('sort_order');
            $table->unsignedTinyInteger('option_no')->default(1)->after('sl_no');
            $table->enum('area_mode', ['sqft', 'nos', 'manual'])->default('manual')->after('option_no');
            // sqft mode: length × width
            $table->decimal('area_length', 10, 2)->nullable()->after('area_mode');
            $table->decimal('area_width',  10, 2)->nullable()->after('area_length');
            // nos mode: count × size
            $table->decimal('area_nos',    10, 2)->nullable()->after('area_width');
            $table->decimal('area_size',   10, 2)->nullable()->after('area_nos');
            // manual / computed area
            $table->decimal('area_value',  10, 2)->nullable()->after('area_size');
            $table->decimal('rate_per_sft',12, 2)->nullable()->after('area_value');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn(['sl_no','option_no','area_mode','area_length','area_width','area_nos','area_size','area_value','rate_per_sft']);
        });
    }
};
