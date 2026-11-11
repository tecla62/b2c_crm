<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::table('asset_items')
            ->whereNotNull('asset_group_code')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get()
            ->groupBy('asset_group_code')
            ->each(function ($assets) {
                $master = $assets->first();
                $extraIds = $assets->pluck('id')->reject(fn($id) => $id === $master->id)->values();

                DB::table('asset_items')->where('id', $master->id)->update([
                    'total_quantity' => $assets->sum('total_quantity'),
                    'issued_quantity' => $assets->sum('issued_quantity'),
                    'damaged_quantity' => $assets->sum('damaged_quantity'),
                    'maintenance_quantity' => $assets->sum('maintenance_quantity'),
                    'lost_quantity' => $assets->sum('lost_quantity'),
                    'minimum_quantity' => $assets->max('minimum_quantity'),
                    'asset_group_code' => null,
                ]);

                if ($extraIds->isNotEmpty()) {
                    DB::table('asset_assignments')->whereIn('asset_item_id', $extraIds)->update(['asset_item_id' => $master->id]);
                    DB::table('asset_movements')->whereIn('asset_item_id', $extraIds)->update(['asset_item_id' => $master->id]);
                    DB::table('asset_items')->whereIn('id', $extraIds)->delete();
                }
            });

        Schema::table('asset_items', function (Blueprint $table) {
            $table->dropIndex(['asset_group_code']);
            $table->dropColumn('asset_group_code');
        });
    }

    public function down(): void
    {
        Schema::table('asset_items', function (Blueprint $table) {
            $table->uuid('asset_group_code')->nullable()->after('asset_code')->index();
        });
    }
};
