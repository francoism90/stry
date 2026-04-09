<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoSorter: string implements Enumerable
{
    case Newest = 'newest';
    case Oldest = 'oldest';
    case Ordered = 'ordered';
    case Longest = 'longest';
    case Shortest = 'shortest';
    case Filesize = 'filesize';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'newest' => __('Newest'),
            'oldest' => __('Oldest'),
            'ordered' => __('Alphabetical'),
            'longest' => __('Longest'),
            'shortest' => __('Shortest'),
            'filesize' => __('File Size'),
        ];
    }
}
