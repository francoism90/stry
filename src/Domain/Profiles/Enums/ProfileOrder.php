<?php

declare(strict_types=1);

namespace Domain\Profiles\Enums;

use Domain\Shared\Contracts\Enumerable;

enum ProfileOrder: string implements Enumerable
{
    case Name = 'name';
    case Newest = 'created_at';

    public function label(): string
    {
        return match ($this) {
            self::Name => __('Name'),
            self::Newest => __('Newest'),
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
