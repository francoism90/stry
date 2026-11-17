<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum VideoSorter: string implements Enumerable
{
    use HasLabels;

    case Recommended = 'recommended';
    case Newest = 'newest';
    case Oldest = 'oldest';
    case Longest = 'longest';
    case Shortest = 'shortest';
    case Ordered = 'ordered';
    case Filesize = 'filesize';

    public function label(): string
    {
        return match ($this) {
            self::Recommended => __('Recommended'),
            self::Newest => __('Newest'),
            self::Oldest => __('Oldest'),
            self::Longest => __('Longest'),
            self::Shortest => __('Shortest'),
            self::Ordered => __('Ordered'),
            self::Filesize => __('File Size'),
        };
    }
}
