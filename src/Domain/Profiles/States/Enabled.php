<?php

declare(strict_types=1);

namespace Domain\Profiles\States;

class Enabled extends ProfileState
{
    public static $name = 'enabled';

    public function label(): string
    {
        return __('Enabled');
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
