<?php

declare(strict_types=1);

namespace Domain\Playlists\Enums;

enum PlaylistType: string
{
    case Packager = 'packager';
    case Streamer = 'streamer';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'packager' => 'Packager',
            'streamer' => 'Streamer',
        ];
    }
}
