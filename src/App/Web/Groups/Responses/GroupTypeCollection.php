<?php

declare(strict_types=1);

namespace App\Web\Groups\Responses;

use Domain\Groups\Enums\GroupType;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class GroupTypeCollection implements ProvidesInertiaProperty
{
    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return $this->values();
    }

    protected function values(): array
    {
        return once(fn () => array_merge([
            ['value' => null, 'label' => 'All'],
        ], GroupType::options()));
    }
}
