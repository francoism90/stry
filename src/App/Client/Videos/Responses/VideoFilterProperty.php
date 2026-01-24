<?php

declare(strict_types=1);

namespace App\Client\Videos\Responses;

use Domain\Videos\Enums\VideoFilter;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoFilterProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?VideoFilter $filter = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?array => $this->getFilter());
    }

    protected function getFilter(): ?array
    {
        if (! $this->filter) {
            return null;
        }

        return [
            'label' => $this->filter->label(),
            'value' => $this->filter->value,
        ];
    }
}
