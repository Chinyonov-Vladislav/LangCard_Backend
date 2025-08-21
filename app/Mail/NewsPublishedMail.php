<?php

namespace App\Mail;

use App\Models\News;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsPublishedMail extends Mailable
{
    use Queueable, SerializesModels;
    private string $langCode;
    private string $headerText;
    private News $news;
    private string $alt_image_news;
    private string $continueButtonText;
    private string $footerTextPartOne;
    private string $footerTextPartTwo;

    private string $userEmail;

    /**
     * Create a new message instance.
     */
    public function __construct(string $langCode, News $news)
    {
        $this->langCode = $langCode;
        $this->news = $news;
        $this->headerText = "Новая публикация";
        $this->alt_image_news = "Изображение новости";
        $this->continueButtonText = "Читать на сайте";
        $this->footerTextPartOne = "Вы получили это письмо, потому что подписаны на новости.";
        $this->footerTextPartTwo = "Если не хотите получать уведомления — отпишитесь в профиле.";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Info About News Published Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.news',
            with: [
                'langCode'=>$this->langCode,
                'news'=>$this->news,
                'headerText'=>$this->headerText,
                'alt_image_news'=>$this->alt_image_news,
                'continueButtonText'=>$this->continueButtonText,
                'footerTextPartOne'=>$this->footerTextPartOne,
                'footerTextPartTwo'=>$this->footerTextPartTwo,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
