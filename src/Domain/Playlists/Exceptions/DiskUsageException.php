<?php

declare(strict_types=1);

namespace Domain\Playlists\Exceptions;

use Exception;
use Illuminate\Support\Number;

class DiskUsageException extends Exception
{
    public static function exceededLimit(string $path, int|float $limit = 0): self
    {
        $freeSpace = Number::fileSize($limit);

        return new self("Disk free space has exceeded for path: `{$path}` which is greater than the minimum allowed {$freeSpace}.");
    }
}
