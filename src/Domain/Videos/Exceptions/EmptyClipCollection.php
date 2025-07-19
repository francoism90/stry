<?php

declare(strict_types=1);

namespace Domain\Videos\Exceptions;

use Exception;

class EmptyClipCollection extends Exception
{
    public static function make(): self
    {
        return new self('The given video has no clips.');
    }
}
