<?php

declare(strict_types=1);

namespace Domain\Playlists\Enums;

use Domain\Shared\Contracts\Enumerable;

enum PlaylistType: string implements Enumerable
{
    case Packager = 'packager';
    case Streamer = 'streamer';

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'packager' => 'Packager',
            'streamer' => 'Streamer',
        ];
    }
}
