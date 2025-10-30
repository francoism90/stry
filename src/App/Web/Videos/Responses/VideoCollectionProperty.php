<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use Domain\Users\Models\User;
use Foundation\Container\Attributes\QueryParameter;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Container\Attributes\RouteParameter;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

class VideoCollectionProperty implements ProvidesInertiaProperties
{
    public function __construct(
        #[CurrentUser] protected ?User $user = null,
        #[RouteParameter('search')] protected ?string $search = null,
        #[QueryParameter('order')] protected ?string $order = null,
    ) {}

    public function toInertiaProperties(RenderContext $context): array
    {
        logger($this->order);

        return [];
    }
}
