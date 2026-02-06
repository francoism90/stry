<?php

declare(strict_types=1);

namespace Domain\Playlists\Enums;

use Domain\Shared\Contracts\Enumerable;

enum PlaylistType: string implements Enumerable
{
    case Packager = 'packager';
    case Streamer = 'streamer';

    public function label(): string
    {
        return match ($this) {
            self::Packager => 'Packager',
            self::Streamer => 'Streamer',
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
