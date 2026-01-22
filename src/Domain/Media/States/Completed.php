<?php

declare(strict_types=1);

namespace Domain\Media\States;

class Completed extends TranscodeState
{
    public static $name = 'completed';

    public function label(): string
    {
        return __('Completed');
    }
}
