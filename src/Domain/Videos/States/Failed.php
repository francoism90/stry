<?php

declare(strict_types=1);

namespace Domain\Videos\States;

class Failed extends VideoState
{
    public static $name = 'failed';

    public function label(): string
    {
        return __('Failed');
    }
}
