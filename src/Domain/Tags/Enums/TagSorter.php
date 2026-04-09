<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

use Domain\Shared\Contracts\Enumerable;

enum TagSorter: string implements Enumerable
{
    case Default = 'recommended';
    case Name = 'name';
    case Videos = 'videos';
    case Newest = 'newest';
    case Oldest = 'oldest';

    public function label(): string
    {
        return match ($this) {
            self::Default => __('Most videos'),
            self::Name => __('Name'),
            self::Videos => __('Most videos'),
            self::Newest => __('Newest'),
            self::Oldest => __('Oldest'),
        };
    }
}
