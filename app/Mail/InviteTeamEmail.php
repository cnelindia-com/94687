<?php

namespace App\Mail;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InviteTeamEmail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public $user;
    public $settings;
    public $template;
    public $inviteeEmail;
    public $team;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Setting $settings, $template, $inviteeEmail = null, $team = null)
    {
        $this->user = $user;
        $this->settings = $settings;
        $this->template = $template;
        $this->inviteeEmail = $inviteeEmail;
        $this->team = $team;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->template?->subject ?? 'Team Invitation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.team-invite',
            with: [
                'user' => $this->user,
                'settings' => $this->settings,
                'template' => $this->template,
                'inviteeEmail' => $this->inviteeEmail,
                'team' => $this->team,
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