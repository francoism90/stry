<?php

declare(strict_types=1);

namespace Domain\Groups\Enums;

use Domain\Shared\Contracts\Enumerable;

enum GroupSorter: string implements Enumerable
{
    case Name = 'name';
    case Videos = 'videos';
    case Newest = 'newest';
    case Oldest = 'oldest';
    case Updated = 'updated';

    public function label(): string
    {
        return match ($this) {
            self::Name => __('Name'),
            self::Videos => __('Most videos'),
            self::Newest => __('Newest'),
            self::Oldest => __('Oldest'),
            self::Updated => __('Recently updated'),
        };
    }
}
