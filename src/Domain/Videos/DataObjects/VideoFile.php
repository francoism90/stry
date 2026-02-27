<?php

declare(strict_types=1);

namespace Domain\Videos\DataObjects;

use Spatie\LaravelData\Dto;

class VideoFile extends Dto
{
    public function __construct(
        public ?string $disk = null,
        public ?string $path = null,
        public ?string $name = null,
        public ?int $size = null,
    ) {}
}
