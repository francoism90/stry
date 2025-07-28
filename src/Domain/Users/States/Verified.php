<?php

declare(strict_types=1);

namespace Domain\Users\States;

class Verified extends UserState
{
    public static $name = 'verified';

    public function label(): string
    {
        return __('Verified');
    }
}
