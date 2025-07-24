<?php

declare(strict_types=1);

namespace Domain\Playlists\Exceptions;

use Exception;
use Illuminate\Support\Number;

class DiskUsageException extends Exception
{
    public static function exceededUsage(string $path, float $maxDiskUsage): self
    {
        $maxDiskUsage = Number::fileSize($maxDiskUsage);

        return new self("Disk usage exceeded for path: `{$path}` which is greater than the maximum allowed {$maxDiskUsage}");
    }
}
