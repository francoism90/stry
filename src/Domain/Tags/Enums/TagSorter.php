<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

use Domain\Shared\Contracts\Enumerable;

enum TagSorter: string implements Enumerable
{
    case Name = 'name';
    case Newest = 'newest';
    case Oldest = 'oldest';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'name' => __('Alphabetical'),
            'newest' => __('Newest'),
            'oldest' => __('Oldest'),
        ];
    }
}
