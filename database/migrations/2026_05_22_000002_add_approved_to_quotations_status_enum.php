<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft','sent','accepted','approved','rejected','expired') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft','sent','accepted','rejected','expired') NOT NULL DEFAULT 'draft'");
    }
};
