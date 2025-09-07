<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordForAccountCreatedByOauthMail extends Mailable
{
    use Queueable, SerializesModels;

    protected string $provider;
    protected string $email;
    protected string $password;
    /**
     * Create a new message instance.
     */
    public function __construct(string $provider, string $email, string $password)
    {
        $this->provider = $provider;
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Authorization data for your new account',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.emailPasswordForAccountCreatedByOauth',
            with: [
                'provider'=> $this->provider,
                'email'=>$this->email,
                'password'=> $this->password,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
