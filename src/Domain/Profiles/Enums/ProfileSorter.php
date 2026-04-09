<?php

declare(strict_types=1);

namespace Domain\Profiles\Enums;

use Domain\Shared\Contracts\Enumerable;

enum ProfileSorter: string implements Enumerable
{
    case Name = 'name';
    case Newest = 'created_at';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'name' => __('Name'),
            'created_at' => __('Newest'),
        ];
    }
}
