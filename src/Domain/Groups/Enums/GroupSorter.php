<?php

declare(strict_types=1);

namespace Domain\Groups\Enums;

use Domain\Shared\Contracts\Enumerable;

enum GroupSorter: string implements Enumerable
{
    case Name = 'name';
    case Videos = 'videos';
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
            'name' => __('Name'),
            'videos' => __('Most videos'),
            'newest' => __('Newest'),
            'oldest' => __('Oldest'),
            'updated' => __('Recently updated'),
        ];
    }
}
