<?php

declare(strict_types=1);

namespace Domain\Transcodes\States;

class Processing extends TranscodeState
{
    public static string $name = 'processing';

    public function label(): string
    {
        return __('Processing');
    }

    public function color(): string
    {
        return 'primary';
    }

    public function icon(): string
    {
        return 'i-lucide-loader-circle';
    }
}
