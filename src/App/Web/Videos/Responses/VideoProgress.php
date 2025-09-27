<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use Domain\Users\Models\User;
use Domain\Videos\Actions\GetVideoProgress;
use Domain\Videos\Models\Video;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoProgress implements ProvidesInertiaProperty
{
    public function __construct(
        protected Video $video,
        protected User $user,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return app(GetVideoProgress::class)->handle($this->video, $this->user);
    }
}
