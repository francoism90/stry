<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoOrder: string implements Enumerable
{
    case Recommended = 'recommended';
    case Newest = 'newest';
    case Oldest = 'oldest';
    case Ordered = 'ordered';
    case Longest = 'longest';
    case Shortest = 'shortest';
    case Filesize = 'filesize';

    public function label(): string
    {
        return match ($this) {
            self::Recommended => __('Recommended'),
            self::Newest => __('Newest'),
            self::Ordered => __('Ordered'),
            self::Longest => __('Longest'),
            self::Shortest => __('Shortest'),
            self::Filesize => __('Filesize'),
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
