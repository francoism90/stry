<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoFilter: string implements Enumerable
{
    case Default = 'default';
    case History = 'history';
    case Watchlist = 'watchlist';
    case Liked = 'liked';

    public function label(): string
    {
        return match ($this) {
            self::Default => __('All'),
            self::History => __('History'),
            self::Watchlist => __('Watch Later'),
            self::Liked => __('Liked'),
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
