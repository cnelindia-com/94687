<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\UserSupport;
use Illuminate\Support\Facades\Storage;

class TicketAction
{
    private string $sender;

    private array $message;

    private string $status;

    private string $activityType;

    private string $notifyTitle;

    private ?string $attachmentPath = null;

    public function __construct(private UserSupport $ticket) {}

    public static function ticket(UserSupport|int|string $ticketOrId): self
    {
        $ticket = $ticketOrId instanceof UserSupport ? $ticketOrId : UserSupport::findByTicketId($ticketOrId);

        return new self($ticket);
    }

    public function fromAdminIfTrue(bool $isAdmin): self
    {
        if ($isAdmin) {
            return $this->fromAdmin();
        }

        return $this->fromUser();
    }

    public function fromAdmin(): self
    {
        $this->sender = 'admin';

        return $this;
    }

    public function fromUser(): self
    {
        $this->sender = 'user';

        return $this;
    }

    public function answer(string $message): self
    {
        $this->status = $this->isSenderAdmin() ? 'Waiting for answer' : 'Answered';

        $this->activityType = __('Support request waiting for your answer');

        $this->notifyTitle = __('Support request answered');

        return $this->message($message);
    }

    public function new(string $message): self
    {
        $this->status = 'Submitted a Ticket';

        $this->activityType = __($this->status);

        $this->notifyTitle = __('Support request submitted');

        return $this->message($message);
    }

    public function withAttachment(?\Illuminate\Http\UploadedFile $file): self
    {
        \Log::info('TicketAction withAttachment called', [
            'file_exists' => $file ? 'yes' : 'no',
            'file_name' => $file ? $file->getClientOriginalName() : 'null',
            'file_valid' => $file ? ($file->isValid() ? 'yes' : 'no') : 'no',
        ]);
        
        if ($file && $file->isValid()) {
            $allowedMimes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
            $extension = strtolower($file->getClientOriginalExtension());
            
            \Log::info('File extension check', [
                'extension' => $extension,
                'allowed' => in_array($extension, $allowedMimes) ? 'yes' : 'no',
            ]);
            
            if (in_array($extension, $allowedMimes)) {
                // Create directory if not exists
                $uploadPath = public_path('uploads/media/images');
                if (!file_exists($uploadPath)) {
                    mkdir($uploadPath, 0755, true);
                }
                
                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $extension;
                $file->move($uploadPath, $filename);
                
                // Store relative path for database (without 'public' prefix)
                $this->attachmentPath = 'uploads/media/images/' . $filename;
                
                \Log::info('File uploaded successfully', [
                    'path' => $this->attachmentPath,
                    'full_path' => public_path($this->attachmentPath),
                    'file_exists' => file_exists(public_path($this->attachmentPath)),
                    'expected_url' => asset($this->attachmentPath),
                ]);
            }
        } else {
            \Log::warning('File not valid or not provided');
        }
        
        return $this;
    }

    public function withAttachmentPath(?string $path): self
    {
        if ($path) {
            $this->attachmentPath = $path;
        }
        return $this;
    }

    private function message(string $message): self
    {
        $this->message = [
            'message'    => $message,
            'sender'     => $this->sender,
            'is_read'    => 0,
            'attachment' => $this->attachmentPath,
        ];

        return $this;
    }
public function send()
    {
        $this->updateStatus();

      $message=  $this->ticket->messages()->create($this->message);

        if ($this->isSenderAdmin()) {
            $this->sendNotify();
        } else {
            $this->createActivity();
        }

         return $message;
    }

    private function sendNotify(): void
    {
        Notify::to(
            $this->ticket->user,
            $this->notifyTitle,
            $this->message['message'],
            route('dashboard.support.view', $this->ticket->ticket_id)
        );
    }

    private function createActivity(): void
    {
        CreateActivity::for(
            $this->ticket->user,
            $this->activityType,
            $this->ticket->subject,
            route('dashboard.support.view', $this->ticket->ticket_id)
        );
    }

    private function isSenderAdmin(): bool
    {
        return $this->sender === 'admin';
    }

    private function updateStatus(): void
    {
        $this->ticket->update([
            'status' => $this->status,
        ]);

    }
}