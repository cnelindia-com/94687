<?php

declare(strict_types=1);

namespace App\Actions;

use App\Jobs\SendConfirmationEmail;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

final class EmailConfirmation
{
    private function __construct(private readonly User $user) {}

    public static function forUser(User $user): self
    {
        return new self($user);
    }

    public function send(): void
    {
        if ($this->shouldSend()) {
            $this->ensureConfirmationCode();
            $this->dispatch();
        }
    }

    public function resend(): void
    {
        $this->send();
    }

    public function confirm(): void
    {
        if ($this->user->isConfirmed()) {
            return;
        }

        $this->user->update([
            'email_confirmation_code' => null,
            'email_confirmed'         => true,
            'status'                  => true,
        ]);

        $this->unlock();
    }

    private function ensureConfirmationCode(): void
    {
        if (! empty($this->user->email_confirmation_code)) {
            return;
        }

        $this->user->update([
            'email_confirmation_code' => Str::random(67),
        ]);

        $this->user->refresh();
    }

    private function unlock(): void
    {
        Cache::forget($this->lockKey());
    }

    private function dispatch(): void
    {
        SendConfirmationEmail::dispatch($this->user->fresh());
    }

    public function shouldSend(): bool
    {
        $key = $this->lockKey();

        if (cache()->has($key)) {
            return false;
        }

        return Cache::remember($key, now()->addHour(), function () {
            return true;
        });
    }

    private function lockKey(): string
    {
        return 'send_confirmation_email_' . $this->user->id;
    }
}
