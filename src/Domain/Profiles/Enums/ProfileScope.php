<?php

declare(strict_types=1);

namespace Domain\Profiles\Enums;

use Domain\Shared\Contracts\Enumerable;

enum ProfileScope: string implements Enumerable
{
    case All = 'all';
    case Kids = 'kids';
    case Primary = 'primary';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'all' => __('All'),
            'kids' => __('Kids'),
            'primary' => __('Primary'),
        ];
    }
}
