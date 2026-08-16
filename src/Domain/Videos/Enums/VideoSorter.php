<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoSorter: string implements Enumerable
{
    case Relevant = 'relevant';
    case Newest = 'newest';
    case Oldest = 'oldest';
    case Longest = 'longest';
    case Shortest = 'shortest';
    case Ordered = 'ordered';
    case Filesize = 'filesize';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'relevant' => __('Relevant'),
            'newest' => __('Newest'),
            'oldest' => __('Oldest'),
            'longest' => __('Longest'),
            'shortest' => __('Shortest'),
            'ordered' => __('Ordered'),
            'filesize' => __('File Size'),
        ];
    }
}
