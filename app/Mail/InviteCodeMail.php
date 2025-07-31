<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class InviteCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    private string $email;
    private string $code;


    /**
     * Create a new message instance.
     */
    public function __construct(string $email, string $code)
    {
        $this->email = $email;
        $this->code = $code;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Invite Code Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $currentYear = Carbon::now()->year;
        return new Content(
            view: 'emails.inviteCode',
            with: [
                'invite_code'=>$this->code,
                'year'=> $currentYear,
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
