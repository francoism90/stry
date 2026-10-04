<?php

declare(strict_types=1);

namespace Domain\Playlists\Enums;

use Domain\Shared\Contracts\Enumerable;

/**
 * How videos are played: from a playlist packaged ahead by the packager or streamer, or straight
 * from the clip with direct play, which packages nothing.
 */
enum PlaybackMode: string implements Enumerable
{
    case Packager = 'packager';
    case Streamer = 'streamer';
    case Direct = 'direct';

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
            'direct' => 'Direct play',
        ];
    }

    /**
     * The type of playlist this mode packages, or null for direct play.
     */
    public function playlistType(): ?PlaylistType
    {
        return match ($this) {
            self::Packager => PlaylistType::Packager,
            self::Streamer => PlaylistType::Streamer,
            self::Direct => null,
        };
    }
}
