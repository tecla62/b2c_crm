<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewCustomerMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Customer $customer) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Customer Added — ' . $this->customer->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-customer',
        );
    }
}
