<?php

declare(strict_types=1);

namespace Domain\Playlists\DataObjects;

use Domain\Playlists\Enums\PlaylistType;
use Domain\Playlists\Models\Playlist;
use Spatie\LaravelData\Dto;

class PlaylistData extends Dto
{
    public function __construct(
        public string $disk = Playlist::getDestinationDisk(),
        public int $segmentDuration = Playlist::getSegmentDuration(),
        public int $fragmentDuration = Playlist::getFragmentDuration(),
        public bool $encryption = Playlist::shouldUseEncryption(),
        public bool $keyRotation = Playlist::shouldUseKeyRotation(),
        public PlaylistType $type = Playlist::getDefaultType(),
        public ?string $encryptionMethod = Playlist::getEncryptionMethod(),
        public ?string $protectionScheme = Playlist::getProtectionScheme(),
        public ?int $keyRotationDuration = Playlist::getKeyRotationDuration(),
    ) {}
}
