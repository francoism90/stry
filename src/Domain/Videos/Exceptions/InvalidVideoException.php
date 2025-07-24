<?php

declare(strict_types=1);

namespace Domain\Videos\Exceptions;

use Domain\Videos\Models\Video;
use Exception;

class InvalidVideoException extends Exception
{
    public static function emptyClipCollection(Video $model): self
    {
        return new self("The video `{$model->getKey()}` has no clips associated with it.");
    }
}
