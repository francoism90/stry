<?php

declare(strict_types=1);

namespace Domain\Users\Enums;

use Domain\Shared\Contracts\Enumerable;

enum LibraryFilter: string implements Enumerable
{
    case Watching = 'watching';
    // case Saved = 'saved';

    public function label(): string
    {
        return match ($this) {
            self::Watching => __('Watching'),
            // self::Saved => __('Saved'),
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
