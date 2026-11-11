<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('due_date');
            $table->text('terms')->nullable()->after('notes');
            $table->text('guarantee')->nullable()->after('terms');
            $table->text('payment')->nullable()->after('guarantee');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['subject', 'terms', 'guarantee', 'payment']);
        });
    }
};
