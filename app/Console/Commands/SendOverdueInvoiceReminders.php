<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendOverdueInvoiceReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-overdue-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send automated email reminders for overdue invoices';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for overdue invoices...');
        
        $overdueInvoices = Invoice::whereNotIn('status', ['paid', 'cancelled', 'draft'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->get();
            
        $count = 0;
        foreach ($overdueInvoices as $invoice) {
            // Only send reminder if it's actually overdue (outstanding amount > 0)
            if ($invoice->outstanding_amount <= 0) {
                continue;
            }
            
            // Mark as overdue if it isn't already
            if ($invoice->status !== 'overdue') {
                $invoice->status = 'overdue';
                $invoice->save();
            }

            // In a real application, you would send an email here using Laravel's Mail facade.
            // Mail::to($invoice->customer->email)->send(new \App\Mail\OverdueInvoiceReminder($invoice));
            
            Log::info("Sent overdue reminder for Invoice #{$invoice->invoice_number} to Customer #{$invoice->customer_id}. Outstanding: {$invoice->outstanding_amount}");
            $count++;
        }
        
        $this->info("Processed {$count} overdue invoice reminders.");
    }
}
