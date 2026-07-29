<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

enum TagSorter: string
{
    case Ordered = 'ordered';
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
            'ordered' => __('Ordered'),
            'newest' => __('Newest'),
            'oldest' => __('Oldest'),
        ];
    }
}
