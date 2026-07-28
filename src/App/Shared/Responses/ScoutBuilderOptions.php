<?php

declare(strict_types=1);

namespace App\Shared\Responses;

use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Spatie\LaravelOptions\Options;

readonly class ScoutBuilderOptions implements ProvidesInertiaProperty
{
    public function __construct(
        protected string $enum,
    ) {}

    public function toInertiaProperty(PropertyContext $context): array
    {
        return Options::forEnum($this->enum)->toArray();
    }
}
