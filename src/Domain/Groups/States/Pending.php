<?php

declare(strict_types=1);

namespace Domain\Groups\States;

class Pending extends GroupState
{
    public static $name = 'pending';

    public function label(): string
    {
        return __('Pending');
    }
}
