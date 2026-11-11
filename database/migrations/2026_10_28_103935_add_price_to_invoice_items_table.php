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
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->default(0)->after('description');
        });

        // Sync existing rate values into price if any exist
        \Illuminate\Support\Facades\DB::statement('UPDATE invoice_items SET price = rate WHERE price = 0 AND rate > 0');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
