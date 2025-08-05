<?php

declare(strict_types=1);

namespace Domain\Playlists\Enums;

enum PlaylistType: string
{
    case Clips = 'clips';
    case Previews = 'previews';

    public function label(): string
    {
        return match ($this) {
            self::Clips => __('Clips'),
            self::Previews => __('Previews'),
        };
    }
}
