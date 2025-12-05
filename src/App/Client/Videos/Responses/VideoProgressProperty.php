<?php

declare(strict_types=1);

namespace App\Client\Videos\Responses;

use Domain\Users\Models\User;
use Domain\Videos\Actions\GetVideoProgress;
use Domain\Videos\Models\Video;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Container\Attributes\RouteParameter;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class VideoProgressProperty implements ProvidesInertiaProperty
{
    public function __construct(
        #[RouteParameter('video')] protected Video $video,
        #[CurrentUser] protected ?User $user = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): int|float => $this->getProgress());
    }

    protected function getProgress(): int|float
    {
        if (! $this->user) {
            return 0;
        }

        return app(GetVideoProgress::class)->handle($this->video, $this->user);
    }
}
