<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public ?string $customBody = null,
        public ?string $customSubject = null,
        public bool $showDesc = true
    ) {
        $this->showDesc = filter_var($showDesc, FILTER_VALIDATE_BOOLEAN);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->customSubject ?? 'Invoice ' . $this->invoice->invoice_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.invoice',
            with: [
                'invoice' => $this->invoice,
                'body'    => $this->customBody ?? $this->getDefaultBody(),
            ],
        );
    }

    public function attachments(): array
    {
        $invoice = $this->invoice;
        $invoice->loadMissing('customer', 'items.category', 'createdBy', 'quotation');
        $showDesc = $this->showDesc;
        $pdfMode  = true;
        $accountSummary = $this->invoiceAccountSummary();

        $pdf = Pdf::loadView('admin.invoices.pdf', compact('invoice', 'showDesc', 'pdfMode', 'accountSummary'))
                  ->setPaper('a4', 'portrait');

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                $this->invoice->invoice_number . '.pdf'
            )->withMime('application/pdf'),
        ];
    }

    private function getDefaultBody(): string
    {
        $companyName = setting('company_name', config('app.name'));

        return "Dear {$this->invoice->customer->name},\n\n"
            . "Please find your Invoice {$this->invoice->invoice_number} attached.\n\n"
            . "Total Amount: " . format_currency($this->invoice->total) . "\n\n"
            . "Kindly process the payment as per the invoice terms.\n\n"
            . "Regards,\n"
            . $companyName;
    }

    private function invoiceAccountSummary(): array
    {
        $paidAmount = Transaction::where('invoice_id', $this->invoice->id)
            ->where('status', 'cleared')
            ->where('direction', 'income')
            ->where('type', 'payment')
            ->sum('amount');

        return [
            'invoice_total' => (float) $this->invoice->total,
            'paid' => (float) $paidAmount,
            'outstanding' => max(0, (float) $this->invoice->total - (float) $paidAmount),
        ];
    }
}
