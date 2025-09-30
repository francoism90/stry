<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use Domain\Videos\Enums\VideoType;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoTypeCollection implements ProvidesInertiaProperty
{
    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return [
            ['value' => null, 'label' => __('All')],
            ...VideoType::options(),
        ];
    }
}
