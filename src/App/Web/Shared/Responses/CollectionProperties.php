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
        #[QueryParameter('view')] protected ?string $view = null,
    ) {}

    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'filter' => fn () => $this->filter,
            'search' => fn () => $this->search,
            'view' => fn () => $this->getViewValue(),
        ];
    }

    protected function getViewValue(): ?string
    {
        return $this->user && blank($this->view)
            ? UserSettings::fromModel($this->user)->appearance->default_view
            : $this->view;
    }
}
