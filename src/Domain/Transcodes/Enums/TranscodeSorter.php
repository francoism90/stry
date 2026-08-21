<?php

declare(strict_types=1);

namespace Domain\Transcodes\Enums;

use Domain\Shared\Contracts\Enumerable;

enum TranscodeSorter: string implements Enumerable
{
    case Newest = 'newest';
    case Oldest = 'oldest';
    case Updated = 'updated';

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
            'updated' => __('Recently updated'),
        ];
    }
}
