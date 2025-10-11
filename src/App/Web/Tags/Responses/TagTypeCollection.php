<?php

declare(strict_types=1);

namespace App\Web\Tags\Responses;

use Domain\Tags\Enums\TagType;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class TagTypeCollection implements ProvidesInertiaProperty
{
    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return TagType::options();
    }
}
