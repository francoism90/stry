<?php

declare(strict_types=1);

namespace App\Web\Settings\Responses;

use Domain\Users\Models\User;
use Foundation\Settings\GeneralSettings;
use Illuminate\Support\Facades\Gate;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class ApplicationSettingsProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?User $user = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?array => $this->getSettings());
    }

    protected function getSettings(): ?array
    {
        if (! $this->user || Gate::denies('manage-application-settings')) {
            return null;
        }

        return app(GeneralSettings::class)->toArray();
    }
}
