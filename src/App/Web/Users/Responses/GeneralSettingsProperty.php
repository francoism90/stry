<?php

declare(strict_types=1);

namespace App\Web\Users\Responses;

use Domain\Users\DataObjects\GeneralSettings;
use Domain\Users\Models\User;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class GeneralSettingsProperty implements ProvidesInertiaProperty
{
    public function __construct(protected ?User $user = null) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        if (! $this->user) {
            return null;
        }

        return once(fn (): GeneralSettings => $this->user->general_settings);
    }
}
