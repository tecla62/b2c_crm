<?php

namespace App\Mail;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Transaction $transaction)
    {
        $this->transaction->loadMissing('customer', 'invoice.customer', 'invoice.quotation', 'createdBy');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Receipt ' . $this->transaction->transaction_number,
        );
    }

    public function content(): Content
    {
        $transaction = $this->transaction;
        $accountSummary = $this->accountSummary();

        return new Content(
            view: 'emails.payment-receipt',
            with: compact('transaction', 'accountSummary'),
        );
    }

    public function attachments(): array
    {
        $transaction = $this->transaction;
        $pdfMode = true;
        $accountSummary = $this->accountSummary();

        $pdf = Pdf::loadView('admin.transactions.receipt', compact('transaction', 'pdfMode', 'accountSummary'))
            ->setPaper('a4', 'portrait');

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                $this->transaction->transaction_number . '-receipt.pdf'
            )->withMime('application/pdf'),
        ];
    }

    private function accountSummary(): ?array
    {
        $invoice = $this->transaction->invoice;

        if (!$invoice) {
            return null;
        }

        $paid = Transaction::where('invoice_id', $invoice->id)
            ->where('status', 'cleared')
            ->where('direction', 'income')
            ->where('type', 'payment')
            ->sum('amount');

        return [
            'invoice_total' => (float) $invoice->total,
            'paid' => (float) $paid,
            'outstanding' => max(0, (float) $invoice->total - (float) $paid),
        ];
    }
}
