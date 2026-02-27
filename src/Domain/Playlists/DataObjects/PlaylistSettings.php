<?php

declare(strict_types=1);

namespace Domain\Playlists\DataObjects;

use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Spatie\LaravelData\Dto;

class PlaylistSettings extends Dto
{
    public string $disk;

    public int $segmentDuration;

    public int $fragmentDuration;

    public bool $encryption;

    public bool $keyRotation;

    public PlaylistType $type;

    public ?string $encryptionMethod;

    public ?string $protectionScheme;

    public ?int $keyRotationDuration;

    public function __construct(?PlaylistType $type = null)
    {
        $this->type = $type ?? Playlist::getDefaultType();
        $this->disk = Playlist::getDestinationDisk();
        $this->segmentDuration = Playlist::getSegmentDuration();
        $this->fragmentDuration = Playlist::getFragmentDuration();
        $this->encryption = Playlist::shouldUseEncryption();
        $this->keyRotation = Playlist::shouldUseKeyRotation();
        $this->encryptionMethod = Playlist::getEncryptionMethod();
        $this->protectionScheme = Playlist::getProtectionScheme();
        $this->keyRotationDuration = Playlist::getKeyRotationDuration();
    }
}
