<?php

declare(strict_types=1);

namespace Foundation\Settings;

use Domain\Playlists\Enums\PlaylistType;
use Spatie\LaravelSettings\Settings;

class PlaylistSettings extends Settings
{
    public PlaylistType $type = PlaylistType::Packager;

    public string $disk_name = 'segments';

    public string $language = 'en';

    public string $text_language = 'en';

    public int $expires_after = 604800;

    public int $manifest_cache_lifetime = 60;

    public int $manifest_url_lifetime = 14400;

    public int $manifest_refresh_before = 300;

    public int $media_url_lifetime = 14400;

    public int $key_url_lifetime = 7200;

    public ?array $protection_systems = null;

    public ?string $protection_scheme = null;

    public ?bool $key_rotation = null;

    public ?int $key_rotation_duration = null;

    public static function group(): string
    {
        return 'playlist';
    }
}
