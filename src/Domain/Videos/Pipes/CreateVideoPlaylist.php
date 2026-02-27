<?php

declare(strict_types=1);

namespace Domain\Videos\Pipes;

use Closure;

class CreateVideoPlaylist
{
    public function handle($video, Closure $next)
    {
        $video->createPlaylist();

        return $next($video);
    }
}
