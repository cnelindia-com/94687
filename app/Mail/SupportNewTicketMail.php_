<?php

namespace App\Mail;

use App\Models\SupportSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportNewTicketMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $ticketId,
        public string $subject,
        public string $messageText,
        public SupportSetting $supportSettings
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = 'New Support Ticket - ' . $this->subject;
        
        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.support-reply',
            with: [
                'userName' => $this->userName,
                'ticketId' => $this->ticketId,
                'messageText' => $this->messageText,
                'supportName' => $this->supportSettings->name,
                'supportEmail' => $this->supportSettings->email,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}