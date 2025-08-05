<?php

declare(strict_types=1);

namespace Domain\Playlists\Enums;

enum PlaylistType: string
{
    case Clip = 'clip';
    case Preview = 'preview';

    public function label(): string
    {
        return match ($this) {
            self::Clip => __('Clip'),
            self::Preview => __('Previews'),
        };
    }
}
