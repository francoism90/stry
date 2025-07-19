<?php

declare(strict_types=1);

namespace Domain\Playlists\Exceptions;

use Exception;

class ExpiredPlaylistException extends Exception
{
    public static function make(): self
    {
        return new self('The playlist has expired and is no longer available.');
    }
}
