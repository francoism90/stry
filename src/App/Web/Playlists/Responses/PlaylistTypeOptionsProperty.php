<?php

declare(strict_types=1);

namespace App\Web\Playlists\Responses;

use Domain\Playlists\Enums\PlaylistType;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Spatie\LaravelOptions\Options;

readonly class PlaylistTypeOptionsProperty implements ProvidesInertiaProperty
{
    public static function options(): Options
    {
        return Options::forEnum(PlaylistType::class);
    }

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): Options => self::options());
    }
}
