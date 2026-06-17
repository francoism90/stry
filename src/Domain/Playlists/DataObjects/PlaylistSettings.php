<?php

declare(strict_types=1);

namespace Domain\Playlists\DataObjects;

use Domain\Playlists\Models\Playlist;
use Spatie\LaravelData\Dto;

class PlaylistSettings extends Dto
{
    public string $disk;

    public string $language;

    public string $textLanguage;

    public int $bufferTime;

    public int $segmentDuration;

    public int $fragmentDuration;

    public bool $encryption;

    public bool $keyRotation;

    public ?string $encryptionMethod = null;

    public ?string $protectionScheme = null;

    public ?int $keyRotationDuration = null;

    public function __construct()
    {
        $this->disk = Playlist::getDestinationDisk();
        $this->language = Playlist::getLanguage();
        $this->textLanguage = Playlist::getTextLanguage();
        $this->bufferTime = Playlist::getBufferTime();
        $this->segmentDuration = Playlist::getSegmentDuration();
        $this->fragmentDuration = Playlist::getFragmentDuration();
        $this->encryption = Playlist::shouldUseEncryption();
        $this->keyRotation = Playlist::shouldUseKeyRotation();
        $this->encryptionMethod = Playlist::getEncryptionMethod();
        $this->protectionScheme = Playlist::getProtectionScheme();
        $this->keyRotationDuration = Playlist::getKeyRotationDuration();
    }
}
