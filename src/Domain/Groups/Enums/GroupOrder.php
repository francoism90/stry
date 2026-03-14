<?php

declare(strict_types=1);

namespace Domain\Groups\Enums;

use Domain\Shared\Contracts\Enumerable;

enum GroupOrder: string implements Enumerable
{
    case Default = 'recommended';
    case Name = 'name';
    case Videos = 'videos';
    case Newest = 'newest';
    case Oldest = 'oldest';
    case Updated = 'updated';

    public function label(): string
    {
        return match ($this) {
            self::Default => __('Recommended'),
            self::Name => __('Name'),
            self::Videos => __('Most videos'),
            self::Newest => __('Newest'),
            self::Oldest => __('Oldest'),
            self::Updated => __('Recently updated'),
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
