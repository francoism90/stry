<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

use Domain\Shared\Contracts\Enumerable;

enum TagSorter: string implements Enumerable
{
    case Ordered = 'ordered';
    case Newest = 'newest';
    case Oldest = 'oldest';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'ordered' => __('Ordered'),
            'newest' => __('Newest'),
            'oldest' => __('Oldest'),
        ];
    }
}
