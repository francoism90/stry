<?php

declare(strict_types=1);

namespace App\Modules\Videos\Responses;

use Domain\Users\Models\User;
use Domain\Videos\Actions\GetVideoProgress;
use Domain\Videos\Models\Video;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoProgressProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Video $video = null,
        protected ?User $user = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        if (! $this->video || ! $this->user) {
            return 0;
        }

        return app(GetVideoProgress::class)->handle($this->video, $this->user);
    }
}
