<?php

declare(strict_types=1);

namespace Modules\Web\Settings\Responses;

use Domain\Users\Models\User;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Support\System\ApplicationNotices;

readonly class ApplicationNoticesProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?User $user = null,
    ) {}

    /**
     * @return array{restart: bool, migrations: bool}|null
     */
    public function toInertiaProperty(PropertyContext $context): ?array
    {
        if (! $this->user?->can('manage-application-settings')) {
            return null;
        }

        return app(ApplicationNotices::class)->toArray();
    }
}
