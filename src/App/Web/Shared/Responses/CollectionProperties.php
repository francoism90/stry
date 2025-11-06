<?php

declare(strict_types=1);

namespace App\Web\Shared\Responses;

use Domain\Users\DataObjects\UserSettings;
use Domain\Users\Models\User;
use Foundation\Container\Attributes\QueryParameter;
use Illuminate\Container\Attributes\CurrentUser;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

readonly class CollectionProperties implements ProvidesInertiaProperties
{
    public function __construct(
        #[CurrentUser] protected ?User $user = null,
        #[QueryParameter('filter')] protected ?string $filter = null,
        #[QueryParameter('search')] protected ?string $search = null,
        #[QueryParameter('grid')] protected ?string $grid = null,
    ) {}

    public function toInertiaProperties(RenderContext $context): array
    {
        $grid = UserSettings::fromModel($this->user)->include('general')->toArray();

        dd($grid);


        return [
            'filter' => fn () => $this->filter,
            'search' => fn () => $this->search,
            'grid' => fn () => $this->grid,
        ];
    }
}
