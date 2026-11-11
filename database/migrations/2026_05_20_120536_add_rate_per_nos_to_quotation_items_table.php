<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('rate_per_nos', 12, 2)->nullable()->after('rate_per_sft');
        });
    }

    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->dropColumn('rate_per_nos');
        });
    }
};
