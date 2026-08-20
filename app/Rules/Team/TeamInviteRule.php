<?php

namespace App\Rules\Team;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TeamInviteRule implements ValidationRule
{
    private const MAX_TEAM_MEMBERS = 5;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $team = request()->route('team');

        if (! $team) {
            return;
        }

        $usedSeats = $team->members()->count();

        if ($usedSeats >= self::MAX_TEAM_MEMBERS) {
            $fail(__('You can add only 5 team members.'));
        }
    }
}
