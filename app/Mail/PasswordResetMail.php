<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    private string $email;
    private string $token;
    /**
     * Create a new message instance.
     */
    public function __construct(string $email, string $token)
    {
        $this->email = $email;
        $this->token = $token;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $url = url('/api/password/reset?token='.$this->token.'&email='.$this->email);

        return new Content(
            view: 'emails.emailResetPasswordLink',
            with: [
                'title'=>__('emails.emailResetPasswordLink.title'),
                'password_reset_request'=>__('emails.emailResetPasswordLink.password_reset_request'),
                'click_link_to_reset_password'=>__('emails.emailResetPasswordLink.click_link_to_reset_password'),
                'reset_password'=>__('emails.emailResetPasswordLink.reset_password'),
                'link_expiration_notice'=>__('emails.emailResetPasswordLink.link_expiration_notice'),
                'url'=> $url,
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
