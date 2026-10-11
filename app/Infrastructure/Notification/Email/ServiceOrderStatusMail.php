<?php

namespace App\Infrastructure\Notification\Email;

use App\Application\Notification\Data\ServiceOrderStatusNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

final class ServiceOrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly ServiceOrderStatusNotification $notification) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->notification->subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.service-order-status',
            with: ['notification' => $this->notification],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
