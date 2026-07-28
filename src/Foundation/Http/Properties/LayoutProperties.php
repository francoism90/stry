<?php

declare(strict_types=1);

namespace Foundation\Http\Properties;

use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

readonly class LayoutProperties implements ProvidesInertiaProperties
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $id = null,
        public ?string $type = null,
    ) {
        //
    }

    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'id' => $this->id,
            'type' => $this->type,
        ];
    }
}
