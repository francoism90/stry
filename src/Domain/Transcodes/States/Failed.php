<?php

declare(strict_types=1);

namespace Domain\Transcodes\States;

class Failed extends TranscodeState
{
    public static string $name = 'failed';

    public function label(): string
    {
        return __('Failed');
    }

    public function color(): string
    {
        return 'error';
    }

    public function icon(): string
    {
        return 'i-lucide-x-circle';
    }
}
