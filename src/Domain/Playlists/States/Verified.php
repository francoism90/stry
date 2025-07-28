<?php

declare(strict_types=1);

namespace Domain\Playlists\States;

class Verified extends PlaylistState
{
    public static $name = 'verified';

    public function label(): string
    {
        return __('Verified');
    }
}
