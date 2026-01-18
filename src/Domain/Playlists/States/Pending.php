<?php

declare(strict_types=1);

namespace Domain\Playlists\States;

class Pending extends PlaylistState
{
    public static $name = 'pending';

    public function label(): string
    {
        return __('Processing');
    }
}
