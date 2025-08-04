<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailTwoFactorAuthorizationMail extends Mailable
{
    use Queueable, SerializesModels;

    private string $code;
    private int $countMinutes;

    /**
     * Create a new message instance.
     */
    public function __construct(string $code, int $countMinutes)
    {
        $this->code = $code;
        $this->countMinutes = $countMinutes;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Email Two Factor Authorization',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.emailTwoFactorVerification',
            with: [
                'code'=>$this->code,
                'count_minutes'=> $this->countMinutes,
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
