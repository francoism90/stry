<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoScope: string implements Enumerable
{
    case All = 'all';
    case Unseen = 'unseen';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'all' => __('All'),
            'unseen' => __('Unseen'),
        ];
    }
}
