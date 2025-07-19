<?php

declare(strict_types=1);

namespace Domain\Media\Exceptions;

use Exception;

class MediaException extends Exception
{
    public static function invalidVideoStream(string $path): self
    {
        return new self("The given video has no valid video streams: `{$path}`");
    }
}
