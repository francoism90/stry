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
}
