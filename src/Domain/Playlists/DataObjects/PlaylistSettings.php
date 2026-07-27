<?php

declare(strict_types=1);

namespace Domain\Playlists\DataObjects;

use Domain\Playlists\Models\Playlist;
use Spatie\LaravelData\Dto;

class PlaylistSettings extends Dto
{
    public string $language;

    public string $textLanguage;

    public bool $encryption;

    public bool $keyRotation;

    public ?string $encryptionMethod = null;

    public ?string $protectionScheme = null;

    public ?int $keyRotationDuration = null;

    public function __construct()
    {
        $this->language = Playlist::getLanguage();
        $this->textLanguage = Playlist::getTextLanguage();
        $this->encryption = Playlist::shouldUseEncryption();
        $this->keyRotation = Playlist::shouldUseKeyRotation();
        $this->encryptionMethod = Playlist::getEncryptionMethod();
        $this->protectionScheme = Playlist::getProtectionScheme();
        $this->keyRotationDuration = Playlist::getKeyRotationDuration();
    }
}
