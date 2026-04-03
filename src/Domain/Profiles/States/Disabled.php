<?php

declare(strict_types=1);

namespace Domain\Profiles\States;

class Disabled extends ProfileState
{
    public static $name = 'disabled';

    public function label(): string
    {
        return __('Disabled');
    }

    public function color(): string
    {
        return 'neutral';
    }

    public function icon(): string
    {
        return 'i-lucide-ban';
    }
}
