<?php

declare(strict_types=1);

namespace Domain\Users\States;

class Pending extends UserState
{
    public static $name = 'pending';

    public function label(): string
    {
        return __('Pending');
    }

    public function color(): string
    {
        return 'neutral';
    }

    public function icon(): string
    {
        return 'i-lucide-clock';
    }
}
