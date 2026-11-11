<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropForeign(['asset_employee_id']);
        });

        DB::statement('ALTER TABLE asset_assignments MODIFY asset_employee_id BIGINT UNSIGNED NULL');

        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->foreign('asset_employee_id')->references('id')->on('asset_employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropForeign(['asset_employee_id']);
        });

        DB::statement('ALTER TABLE asset_assignments MODIFY asset_employee_id BIGINT UNSIGNED NOT NULL');

        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->foreign('asset_employee_id')->references('id')->on('asset_employees')->cascadeOnDelete();
        });
    }
};
