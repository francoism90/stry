<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Videos\Requests\VideoIndexRequest;
use Domain\Users\Models\User;
use Domain\Videos\Enums\VideoOrder;
use Foundation\Container\Attributes\QueryParameter;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Http\Request;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperties;
use Inertia\ProvidesInertiaProperty;
use Inertia\RenderContext;

readonly class VideoCollectionProperty implements ProvidesInertiaProperties
{
    public function __construct(
        #[CurrentUser] protected ?User $user = null,
        #[RouteParameter('search')] protected ?string $search = null,
        #[QueryParameter('q') ] protected ?string $q = null,
    ) { }

    public function toInertiaProperties(RenderContext $context): array
    {
        dd($this->q);

        return [
            'canEdit' => $this->user->can('edit'),
            'canDelete' => $this->user->can('delete'),
            'canPublish' => $this->user->can('publish'),
            'isAdmin' => $this->user->hasRole('admin'),
        ];
    }
}
