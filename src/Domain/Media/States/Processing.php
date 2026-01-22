<?php

declare(strict_types=1);

namespace Domain\Media\States;

class Processing extends TranscodeState
{
    public static $name = 'processing';

    public function label(): string
    {
        return __('Processing');
    }
}
