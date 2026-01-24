<?php

declare(strict_types=1);

namespace Domain\Groups\States;

class Verified extends GroupState
{
    public static $name = 'verified';

    public function label(): string
    {
        return __('Verified');
    }

    public function color(): string
    {
        return 'success';
    }

    public function icon(): string
    {
        return 'i-lucide-check-circle';
    }
}
