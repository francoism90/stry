<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

use Domain\Shared\Contracts\Enumerable;

enum TagOrder: string implements Enumerable
{
    case Default = 'recommended';
    case Name = 'name';
    case Newest = 'newest';
    case Oldest = 'oldest';

    public function label(): string
    {
        return match ($this) {
            self::Default => __('Most videos'),
            self::Name => __('Name'),
            self::Newest => __('Newest'),
            self::Oldest => __('Oldest'),
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
