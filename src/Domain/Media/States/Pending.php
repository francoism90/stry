<?php

declare(strict_types=1);

namespace Domain\Media\States;

class Pending extends TranscodeState
{
    public static $name = 'pending';

    public function label(): string
    {
        return __('Pending');
    }
}
