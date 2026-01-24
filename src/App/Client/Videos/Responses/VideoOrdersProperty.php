<?php

declare(strict_types=1);

namespace App\Client\Videos\Responses;

use Domain\Videos\Enums\VideoOrder;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoOrdersProperty implements ProvidesInertiaProperty
{
    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?array => VideoOrder::options());
    }
}
