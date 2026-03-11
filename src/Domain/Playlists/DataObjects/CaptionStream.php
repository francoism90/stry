<?php

declare(strict_types=1);

namespace Domain\Playlists\DataObjects;

use Spatie\LaravelData\Dto;

class CaptionStream extends Dto
{
    public function __construct(
        public ?int $id = null,
        public ?string $disk = null,
        public ?string $path = null,
        public ?string $language = null,
    ) {}
}
