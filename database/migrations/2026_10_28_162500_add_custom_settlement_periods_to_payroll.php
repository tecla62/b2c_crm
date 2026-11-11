<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Change enum to varchar for flexible salary schemes (weekly, monthly, 40_days, 60_days, 70_days, custom, daily_ondemand)
        DB::statement("ALTER TABLE asset_employees MODIFY COLUMN salary_type VARCHAR(50) DEFAULT 'weekly'");
        
        if (!Schema::hasColumn('asset_employees', 'settlement_days')) {
            Schema::table('asset_employees', function (Blueprint $table) {
                $table->integer('settlement_days')->nullable()->default(7)->after('salary_type');
            });
        }

        DB::statement("ALTER TABLE payrolls MODIFY COLUMN period_type VARCHAR(50) DEFAULT 'weekly'");
    }

    public function down()
    {
        if (Schema::hasColumn('asset_employees', 'settlement_days')) {
            Schema::table('asset_employees', function (Blueprint $table) {
                $table->dropColumn('settlement_days');
            });
        }
    }
};
