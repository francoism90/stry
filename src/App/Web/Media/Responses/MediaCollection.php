<?php

declare(strict_types=1);

namespace App\Web\Media\Responses;

use App\Api\Media\Resources\MediaResource;
use Domain\Media\Models\Media;
use Domain\Users\Models\User;
use Foundation\Container\Attributes\QueryParameter;
use Illuminate\Container\Attributes\CurrentUser;
use Inertia\Inertia;
use Inertia\ProvidesInertiaProperties;
use Inertia\RenderContext;

class MediaCollection implements ProvidesInertiaProperties
{
    public function __construct(
        #[CurrentUser] protected ?User $user = null,
        #[QueryParameter('search')] protected ?string $search = null,
    ) {}

    public function toInertiaProperties(RenderContext $context): array
    {

        return [
            'search' => fn() => $this->search,
            'items' =>  fn () => MediaResource::collection(
                $this->getBuilder(),
            ),
        ];
    }

    protected function getBuilder(): mixed
    {
        return Media::query()
            ->paginate(1);
    }
}
