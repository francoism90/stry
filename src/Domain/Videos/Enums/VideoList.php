<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoList: string implements Enumerable
{
    case All = 'all';
    case Watched = 'watched';
    case Shorts = 'shorts';

    public function label(): string
    {
        return match ($this) {
            self::All => __('All'),
            self::Watched => __('Watched'),
            self::Shorts => __('Shorts'),
        };
    }

    public static function options(): array
    {
        return array_map(fn (self $type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ], self::cases());
    }
}
