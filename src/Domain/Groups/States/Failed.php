<?php

declare(strict_types=1);

namespace Domain\Groups\States;

class Failed extends GroupState
{
    public static $name = 'failed';

    public function label(): string
    {
        return __('Failed');
    }
}
