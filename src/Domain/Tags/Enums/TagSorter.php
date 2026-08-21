<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

use Domain\Shared\Contracts\Enumerable;

enum TagSorter: string implements Enumerable
{
    case Videos = 'videos';
    case Newest = 'newest';
    case Oldest = 'oldest';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'videos' => __('Videos'),
            'newest' => __('Newest'),
            'oldest' => __('Oldest'),
        ];
    }
}
