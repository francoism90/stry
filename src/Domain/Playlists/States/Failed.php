<?php

declare(strict_types=1);

namespace Domain\Playlists\States;

class Failed extends PlaylistState
{
    public static $name = 'failed';

    public function label(): string
    {
        return __('Failed');
    }
}
