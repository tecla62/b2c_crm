<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['unit', 'tax_percent']);
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->string('unit')->default('nos')->after('price');
            $table->decimal('tax_percent', 5, 2)->default(0)->after('unit');
        });
    }
};
