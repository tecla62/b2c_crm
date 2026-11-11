<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeUserMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public User    $user,
        public string  $plainPassword,
        public ?string $inviteUrl = null
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to ' . setting('company_name', config('app.name')) . ' — Your Account Details',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-user',
        );
    }
}
