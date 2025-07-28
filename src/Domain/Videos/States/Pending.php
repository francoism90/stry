<?php

declare(strict_types=1);

namespace Domain\Videos\States;

class Pending extends VideoState
{
    public static $name = 'pending';

    public function label(): string
    {
        return __('Pending');
    }
}
