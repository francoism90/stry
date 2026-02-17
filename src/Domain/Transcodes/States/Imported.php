<?php

declare(strict_types=1);

namespace Domain\Transcodes\States;

class Imported extends TranscodeState
{
    public static $name = 'imported';

    public function label(): string
    {
        return __('Imported');
    }

    public function color(): string
    {
        return 'blue';
    }

    public function icon(): string
    {
        return 'i-lucide-file-check';
    }
}
