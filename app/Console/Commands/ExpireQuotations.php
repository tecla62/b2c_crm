<?php

namespace App\Console\Commands;

use App\Models\Quotation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpireQuotations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'quotations:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically expire pending quotations that have passed their valid-until date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to check for expired quotations...');
        
        $quotations = Quotation::whereIn('status', ['draft', 'sent'])
            ->whereNotNull('valid_until')
            ->whereDate('valid_until', '<', now())
            ->get();
            
        $count = 0;
        foreach ($quotations as $quotation) {
            $quotation->status = 'expired';
            $quotation->save();
            $count++;
        }
        
        $this->info("Successfully expired {$count} quotations.");
        Log::info("ExpireQuotations command ran: Expired {$count} quotations.");
    }
}
