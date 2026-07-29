<?php

declare(strict_types=1);

namespace Domain\Profiles\Enums;

enum ProfileSorter: string
{
    case Name = 'name';
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
            'name' => __('Name'),
            'newest' => __('Newest'),
            'oldest' => __('Oldest'),
        ];
    }
}
