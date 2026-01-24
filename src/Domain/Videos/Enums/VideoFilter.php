<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoFilter: string implements Enumerable
{
    case Default = 'all';
    case History = 'viewed';
    case Liked = 'liked';
    case Saved = 'saved';

    public function label(): string
    {
        return match ($this) {
            self::Default => __('All'),
            self::History => __('History'),
            self::Liked => __('Liked'),
            self::Saved => __('Saved'),
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
