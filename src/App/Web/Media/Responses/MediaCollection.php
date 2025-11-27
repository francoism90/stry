<?php

declare(strict_types=1);

namespace App\Web\Media\Responses;

use Domain\Users\Models\User;
use Foundation\Container\Attributes\QueryParameter;
use Illuminate\Container\Attributes\CurrentUser;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

class MediaCollection implements ProvidesInertiaProperty
{
    public function __construct(
        #[CurrentUser] protected ?User $user = null,
        #[QueryParameter('search')] protected ?string $search = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {


        return [
        ];
    }
}
