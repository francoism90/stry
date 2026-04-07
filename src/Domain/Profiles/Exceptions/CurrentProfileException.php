<?php

declare(strict_types=1);

namespace Domain\Profiles\Exceptions;

use Exception;

class CurrentProfileException extends Exception
{
    public static function notAvailable(): self
    {
        return new self('Current profile is not available.');
    }
}
