<?php

declare(strict_types=1);

namespace Domain\Videos\DataObjects;

use Spatie\LaravelData\Data;

class VideoFileData extends Data
{
    public function __construct(
        public ?string $disk = null,
        public ?string $path = null,
        public ?string $name = null,
        public ?int $size = null,
    ) {}
}
