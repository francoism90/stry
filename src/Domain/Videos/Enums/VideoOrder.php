<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoOrder: string implements Enumerable
{
    case Default = 'recommended';
    case Newest = 'newest';
    case Oldest = 'oldest';
    case Ordered = 'ordered';
    case Longest = 'longest';
    case Shortest = 'shortest';
    case Filesize = 'filesize';

    public function label(): string
    {
        return match ($this) {
            self::Default => __('Recommended'),
            self::Newest => __('Newest'),
            self::Oldest => __('Oldest'),
            self::Ordered => __('Alphabetical'),
            self::Longest => __('Longest'),
            self::Shortest => __('Shortest'),
            self::Filesize => __('File Size'),
        };
    }
}
