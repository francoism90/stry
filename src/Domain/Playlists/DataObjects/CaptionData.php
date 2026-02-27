<?php

declare(strict_types=1);

namespace Domain\Playlists\DataObjects;

use Spatie\LaravelData\Dto;

class CaptionData extends Dto
{
    public function __construct(
        public ?string $disk = null,
        public ?string $path = null,
        public ?string $language = null,
    ) {}
}
