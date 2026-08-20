<?php

declare(strict_types=1);

namespace App\Domains\Entity\Concerns\Input;

use RuntimeException;

trait HasInputMinute
{
    protected int $inputMinute;

    public function getInputMinute(): int
    {
        if (! isset($this->inputMinute)) {
            throw new RuntimeException('Input is not provided');
        }

        return $this->inputMinute;
    }

    public function inputMinute(int $inputMinute): static
    {
        $this->inputMinute = $inputMinute;

        return $this;
    }
}
