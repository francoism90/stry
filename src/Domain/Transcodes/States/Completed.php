<?php

declare(strict_types=1);

namespace Domain\Transcodes\States;

class Completed extends TranscodeState
{
    public static $name = 'completed';

    public function label(): string
    {
        return __('Completed');
    }

    public function color(): string
    {
        return 'success';
    }

    public function icon(): string
    {
        return 'i-lucide-check-circle';
    }
}
