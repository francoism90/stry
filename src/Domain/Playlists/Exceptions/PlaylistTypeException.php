<?php

declare(strict_types=1);

namespace Domain\Playlists\Exceptions;

use Domain\Playlists\Enums\PlaylistType;
use Exception;

class PlaylistTypeException extends Exception
{
    public static function invalidType(PlaylistType|string $type): self
    {
        return new self("Playlist type `{$type}` is not supported");
    }
}
