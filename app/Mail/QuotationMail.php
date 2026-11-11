<?php

namespace App\Mail;

use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $quotation;
    public $customBody;
    public $customSubject;
    public $showDesc;

    public function __construct(Quotation $quotation, $customBody = null, $customSubject = null, $showDesc = true)
    {
        $this->quotation = $quotation;
        $this->customBody = $customBody;
        $this->customSubject = $customSubject;
        $this->showDesc = filter_var($showDesc, FILTER_VALIDATE_BOOLEAN);
    }

    public function envelope(): Envelope
    {
        $subject = $this->customSubject ?? 'Quotation ' . $this->quotation->quotation_number;
        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $body = $this->customBody ?? $this->getDefaultBody();
        
        return new Content(
            view: 'emails.quotation',
            with: [
                'quotation' => $this->quotation,
                'body' => $body,
            ],
        );
    }

    public function attachments(): array
    {
        $quotation = $this->quotation;
        $quotation->loadMissing('customer', 'items.category');
        $showDesc = $this->showDesc;

        $pdf = Pdf::loadView('admin.quotations.pdf', compact('quotation', 'showDesc'))
                  ->setPaper('a4', 'portrait');
        
        $pdfContent = $pdf->output();
        
        return [
            Attachment::fromData(fn () => $pdfContent, $this->quotation->quotation_number . '.pdf')
                ->withMime('application/pdf'),
        ];
    }

    private function getDefaultBody(): string
    {
        $companyName = setting('company_name', config('app.name'));
        $currency = setting('currency_symbol', '₹');
        
        return "Dear {$this->quotation->customer->name},\n\n"
            . "Please find your Quotation {$this->quotation->quotation_number} attached.\n\n"
            . "Total Amount: {$currency} " . number_format($this->quotation->total, 2) . "\n\n"
            . "Kindly review and revert at your earliest convenience.\n\n"
            . "Regards,\n"
            . $companyName;
    }
}