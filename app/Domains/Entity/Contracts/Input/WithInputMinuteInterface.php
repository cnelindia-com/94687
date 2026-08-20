<?php

declare(strict_types=1);

namespace App\Domains\Entity\Contracts\Input;

interface WithInputMinuteInterface
{
    public function getInputMinute(): int;

    public function inputMinute(int $input): static;
}
