<?php

declare(strict_types=1);

namespace Domain\Groups\Enums;

enum GroupType: string
{
    case System = 'system';
    case Mixer = 'mixer';
    case Private = 'private';
    case Public = 'public';

    public function label(): string
    {
        return match ($this) {
            self::System => __('System'),
            self::Mixer => __('Mixer'),
            self::Private => __('Private'),
            self::Public => __('Public'),
        };
    }
}
