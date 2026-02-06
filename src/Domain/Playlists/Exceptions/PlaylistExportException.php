<?php

declare(strict_types=1);

namespace Domain\Playlists\Exceptions;

use Exception;

class PlaylistExportException extends Exception
{
    public static function exportFailed(): self
    {
        return new self('Failed to export the playlist. See logs for details.');
    }

    public static function copyFailed(int $count): self
    {
        return new self("Failed to copy {$count} files to storage. See logs for details.");
    }
}
