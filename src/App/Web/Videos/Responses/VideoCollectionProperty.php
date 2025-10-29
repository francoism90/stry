<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use Domain\Users\Models\User;
use Domain\Videos\Enums\VideoOrder;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Container\Attributes\RouteParameter;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

 class VideoCollectionProperty implements ProvidesInertiaProperty
{
    public function __construct(
        #[CurrentUser] protected User $user,
        #[RouteParameter('filter')] protected ?string $filter = null,
    )
    {

    }

    public function toInertiaProperty(PropertyContext $context): mixed
    {

        dd($context);

        return VideoOrder::options();
    }
}
