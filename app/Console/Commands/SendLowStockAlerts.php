<?php

namespace App\Console\Commands;

use App\Models\AssetItem;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendLowStockAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assets:low-stock-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send alerts for assets that are below their minimum quantity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for low stock assets...');
        
        $lowStockAssets = AssetItem::where('status', 'active')
            ->get()
            ->filter(fn($asset) => $asset->available_quantity <= $asset->minimum_quantity);
            
        if ($lowStockAssets->isEmpty()) {
            $this->info('No low stock assets found.');
            return;
        }

        // Send a notification to all admins (users with assets.view or similar role)
        // For simplicity in this CRM, we can log it and optionally send to a main admin email
        // Or if there's an in-app notification system, we can create one here.
        
        $message = "Low Stock Alert: " . $lowStockAssets->count() . " items are low on stock.";
        foreach ($lowStockAssets as $asset) {
            $message .= "\n- {$asset->name} ({$asset->asset_code}): {$asset->available_quantity} available (Min: {$asset->minimum_quantity})";
        }
        
        Log::warning($message);
        
        $this->info("Low stock alerts generated for {$lowStockAssets->count()} items.");
    }
}
