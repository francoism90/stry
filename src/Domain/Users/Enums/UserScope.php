<?php

declare(strict_types=1);

namespace Domain\Users\Enums;

use Domain\Shared\Contracts\Enumerable;

enum UserScope: string implements Enumerable
{
    case All = 'all';
    case Verified = 'verified';
    case Unverified = 'unverified';
    case Deleted = 'deleted';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'all' => __('All'),
            'verified' => __('Verified'),
            'unverified' => __('Unverified'),
            'deleted' => __('Deleted'),
        ];
    }
}
