<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('asset_items', function (Blueprint $table) {
            $table->uuid('asset_group_code')->nullable()->after('asset_code')->index();
        });

        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->uuid('assignment_group_code')->nullable()->after('asset_item_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropColumn('assignment_group_code');
        });

        Schema::table('asset_items', function (Blueprint $table) {
            $table->dropColumn('asset_group_code');
        });
    }
};
