<?php

namespace App\Mail;

use App\Models\SupportSetting;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportReplyMail extends Mailable
{
    use SerializesModels;

    public ?string $attachmentCid = null;

    public function __construct(
        public string $userName,
        public string $userEmail,
        public string $ticketId,
        public string $messageText,
        public SupportSetting $supportSettings,
        public bool $isSuperAdmin = false,
        public ?string $attachmentPath = null
    ) {
        // Generate a unique CID for the attachment
        $this->attachmentCid = $this->attachmentPath ? md5($this->attachmentPath) : null;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->supportSettings->name . ' - Reply to Support Ticket #' . $this->ticketId;
        
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        \Log::info('Mail content - attachmentPath', [
            'attachmentPath' => $this->attachmentPath,
        ]);
        
        return new Content(
            view: 'mail.support-reply',
            with: [
                'userName' => $this->userName,
                'userEmail' => $this->userEmail,
                'ticketId' => $this->ticketId,
                'messageText' => $this->messageText,
                'supportName' => $this->supportSettings->name,
                'supportEmail' => $this->supportSettings->email,
                'isSuperAdmin' => $this->isSuperAdmin,
                'attachmentPath' => $this->attachmentPath,
                'attachmentCid' => $this->attachmentCid,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        \Log::info('Checking email attachment', [
            'path' => $this->attachmentPath,
            'full_path' => public_path($this->attachmentPath),
            'exists' => $this->attachmentPath ? file_exists(public_path($this->attachmentPath)) : false,
        ]);
        
        if ($this->attachmentPath && file_exists(public_path($this->attachmentPath))) {
            try {
                // Determine mime type from extension
                $extension = strtolower(pathinfo($this->attachmentPath, PATHINFO_EXTENSION));
                $mimeType = match($extension) {
                    'jpg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'webp' => 'image/webp',
                    default => 'image/png',
                };
                
                // Create attachment with CID for inline display
                $attachment = Attachment::fromPath(public_path($this->attachmentPath))
                    ->as('attachment.' . $extension)
                    ->withMime($mimeType);
                    
                \Log::info('Attachment created successfully', [
                    'cid' => $this->attachmentCid,
                    'mime' => $mimeType,
                ]);
                return [$attachment];
            } catch (\Exception $e) {
                \Log::error('Failed to create attachment', ['error' => $e->getMessage()]);
            }
        }
        \Log::warning('No attachment found');
        return [];
    }
}