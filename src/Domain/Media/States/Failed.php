<?php

declare(strict_types=1);

namespace Domain\Media\States;

class Failed extends TranscodeState
{
    public static $name = 'failed';

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
