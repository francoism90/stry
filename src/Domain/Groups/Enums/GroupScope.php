<?php

declare(strict_types=1);

namespace Domain\Groups\Enums;

use Domain\Shared\Contracts\Enumerable;

enum GroupScope: string implements Enumerable
{
    case All = 'all';
    case Custom = 'custom';
    case Mixer = 'mixer';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'all' => __('All'),
            'custom' => __('Custom'),
            'mixer' => __('Mixer'),
        ];
    }
}
