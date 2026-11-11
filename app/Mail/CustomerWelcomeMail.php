<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerWelcomeMail extends Mailable
{
    use SerializesModels;

    public function __construct(public Customer $customer) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . setting('company_name', config('app.name')) . ' — We\'re glad to have you!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-welcome',
        );
    }
}
