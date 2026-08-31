<?php

declare(strict_types=1);

namespace Domain\Playlists\Settings;

use Domain\Playlists\Enums\EncryptionMethod;
use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Enums\ProtectionScheme;
use Domain\Shared\Enums\Language;
use Spatie\LaravelSettings\Settings;

class PlaylistSettings extends Settings
{
    public PlaylistType $type = PlaylistType::Packager;

    public string $disk_name = 'segments';

    public Language $language = Language::English;

    public Language $text_language = Language::English;

    public int $expires_after = 1209600;

    public int $manifest_cache_lifetime = 300;

    public int $manifest_url_lifetime = 14400;

    public int $manifest_refresh_before = 300;

    public int $media_url_lifetime = 14400;

    public int $key_url_lifetime = 7200;

    public ?EncryptionMethod $encryption = null;

    public ?ProtectionScheme $protection_scheme = null;

    public bool $key_rotation = false;

    public int $key_rotation_duration = 300;

    public static function group(): string
    {
        return 'playlist';
    }
}
