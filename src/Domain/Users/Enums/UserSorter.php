<?php

declare(strict_types=1);

namespace Domain\Users\Enums;

use Domain\Shared\Contracts\Enumerable;

enum UserSorter: string implements Enumerable
{
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
            'newest' => __('Newest'),
            'oldest' => __('Oldest'),
        ];
    }
}
