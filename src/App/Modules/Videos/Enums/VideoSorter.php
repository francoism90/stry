<?php

declare(strict_types=1);

namespace Modules\Videos\Enums;

enum VideoSorter: string
{
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
            'newest' => __('Newest'),
            'oldest' => __('Oldest'),
            'longest' => __('Longest'),
            'shortest' => __('Shortest'),
            'ordered' => __('Ordered'),
            'filesize' => __('File Size'),
        ];
    }
}
