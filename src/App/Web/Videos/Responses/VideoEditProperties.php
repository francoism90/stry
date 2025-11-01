<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Videos\Resources\VideoResource;
use Domain\Users\Models\User;
use Domain\Videos\Actions\GetVideoProgress;
use Domain\Videos\Models\Video;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Container\Attributes\RouteParameter;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

readonly class VideoEditProperties implements ProvidesInertiaProperties
{
    public function __construct(
        #[CurrentUser] protected ?User $user,
        #[RouteParameter('video')] protected Video $video,
    ) {}

    public function toInertiaProperties(RenderContext $context): array
    {
        return [
            'video' => fn () => $this->getVideoResource(),
            'progress' => fn () => app(GetVideoProgress::class)->handle($this->video, $this->user),
        ];
    }

    protected function getVideoResource(): VideoResource
    {
        // Append necessary attributes for the edit form
        $appends = [
            'titles',
            'content',
            'summary',
            'snapshot',
        ];

        return $this->video
            ->loadMissing('media', 'tags', 'user')
            ->append($appends)
            ->toResource(VideoResource::class);
    }
}
