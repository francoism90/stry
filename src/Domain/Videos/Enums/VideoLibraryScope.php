<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoLibraryScope: string implements Enumerable
{
    case All = 'all';
    case Verified = 'verified';
    case Pending = 'pending';
    case Failed = 'failed';

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
            'pending' => __('Pending'),
            'failed' => __('Failed'),
        ];
    }
}
