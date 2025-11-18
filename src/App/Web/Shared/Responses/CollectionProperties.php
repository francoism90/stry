<?php

declare(strict_types=1);

namespace App\Web\Shared\Responses;

use Domain\Users\Actions\UpdateUserSettings;
use Domain\Users\DataObjects\UserSettings;
use Domain\Users\Models\User;
use Foundation\Container\Attributes\QueryParameter;
use Illuminate\Container\Attributes\CurrentUser;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

class CollectionProperties implements ProvidesInertiaProperties
{
    private ?UserSettings $cachedSettings = null;

    public function __construct(
        #[CurrentUser] protected ?User $user = null,
        #[QueryParameter('filter')] protected ?string $filter = null,
        #[QueryParameter('search')] protected ?string $search = null,
        #[QueryParameter('view')] protected ?string $view = null,
    ) {
        $this->storeView();
    }

    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'filter' => fn () => $this->getFilter(),
            'search' => fn () => $this->getSearch(),
            'view' => fn () => $this->getView(),
        ];
    }

    protected function getFilter(): ?string
    {
        return $this->filter;
    }

    protected function getSearch(): ?string
    {
        return $this->search;
    }

    protected function storeView(): void
    {
        if (! $this->user || $this->view === null) {
            return;
        }

        // Only update if the view has changed
        if ($this->view !== $this->getDefaultView()) {
            app(UpdateUserSettings::class)->handle($this->user, [
                'appearance' => [
                    'default_view' => $this->view,
                ],
            ]);
        }
    }

    protected function getView(): ?string
    {
        return $this->view ?? $this->getDefaultView();
    }

    protected function getDefaultView(): ?string
    {
        return $this->getSettings()?->appearance?->default_view;
    }

    protected function getSettings(): ?UserSettings
    {
        if (! $this->user) {
            return null;
        }

        return $this->cachedSettings ??= UserSettings::fromModel($this->user)->include('appearance');
    }
}
