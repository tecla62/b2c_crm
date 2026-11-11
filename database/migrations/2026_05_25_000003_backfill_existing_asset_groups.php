<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        DB::table('asset_items')
            ->whereNull('asset_group_code')
            ->whereNull('deleted_at')
            ->get()
            ->groupBy(fn($asset) => implode('|', [
                $asset->name,
                $asset->category,
                $asset->unit,
                $asset->location,
                $asset->minimum_quantity,
                $asset->condition,
                $asset->status,
                $asset->notes,
                $asset->created_by,
                $asset->created_at,
            ]))
            ->filter(fn($assets) => $assets->count() > 1)
            ->each(function ($assets) {
                DB::table('asset_items')
                    ->whereIn('id', $assets->pluck('id'))
                    ->update(['asset_group_code' => (string) Str::uuid()]);
            });

        DB::table('asset_assignments')
            ->whereNull('assignment_group_code')
            ->get()
            ->groupBy(fn($assignment) => implode('|', [
                $assignment->asset_employee_id,
                $assignment->customer_id,
                $assignment->issue_date,
                $assignment->status,
                $assignment->notes,
                $assignment->created_by,
                $assignment->created_at,
            ]))
            ->filter(fn($assignments) => $assignments->count() > 1)
            ->each(function ($assignments) {
                DB::table('asset_assignments')
                    ->whereIn('id', $assignments->pluck('id'))
                    ->update(['assignment_group_code' => (string) Str::uuid()]);
            });
    }

    public function down(): void
    {
        // Existing grouping data is intentionally retained when rolling back this backfill.
    }
};
