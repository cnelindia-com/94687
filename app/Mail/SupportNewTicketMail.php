<?php

namespace App\Mail;

use App\Models\SupportSetting;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportNewTicketMail extends Mailable
{
    use SerializesModels;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $ticketId,
        public string $ticketSubject,
        public string $messageText,
        public SupportSetting $supportSettings
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            replyTo: [
                new Address($this->userEmail, $this->userName),
            ],
            subject: 'New Support Ticket - ' . $this->ticketSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.support-reply',
            with: [
                'userName'      => $this->userName,
                'userEmail'     => $this->userEmail,
                'ticketId'      => $this->ticketId,
                'messageText'   => $this->messageText,
                'supportName'   => $this->supportSettings->name,
                'supportEmail'  => $this->supportSettings->email,
                'isSuperAdmin'  => false,
                'attachmentPath'=> null,
                'attachmentCid' => null,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
