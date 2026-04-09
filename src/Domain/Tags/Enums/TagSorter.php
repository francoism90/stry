<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

use Domain\Shared\Contracts\Enumerable;

enum TagSorter: string implements Enumerable
{
    case Name = 'name';
    case Newest = 'newest';
    case Oldest = 'oldest';

    public function label(): string
    {
        return match ($this) {
            self::Name => __('0-9, A-Z'),
            self::Newest => __('Newest'),
            self::Oldest => __('Oldest'),
        };
    }
}
