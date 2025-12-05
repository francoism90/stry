<?php

declare(strict_types=1);

namespace Domain\Media\Enums;

use Domain\Shared\Contracts\Enumerable;

enum MediaSort: string implements Enumerable
{
    case Newest = 'newest';
    case Filesize = 'filesize';

    public function label(): string
    {
        return match ($this) {
            self::Newest => __('Newest'),
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
