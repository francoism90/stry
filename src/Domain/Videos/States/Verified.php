<?php

declare(strict_types=1);

namespace Domain\Videos\States;

class Verified extends VideoState
{
    public static $name = 'verified';

    public function label(): string
    {
        return __('Verified');
    }
}
