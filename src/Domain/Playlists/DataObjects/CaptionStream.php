<?php

declare(strict_types=1);

namespace Domain\Playlists\DataObjects;

use Spatie\LaravelData\Dto;

class CaptionStream extends Dto
{
    public function __construct(
        public int $id,
        public string $disk,
        public string $path,
        public ?string $language = null,
    ) {}
}
