<?php

declare(strict_types=1);

namespace App\Web\Videos\Responses;

use App\Api\Videos\Requests\VideoIndexRequest;
use Domain\Users\Models\User;
use Domain\Videos\Enums\VideoOrder;
use Foundation\Container\Attributes\FormInput;
use Foundation\Container\Attributes\FormRequest;
use Foundation\Container\Attributes\QueryParameter;
use Foundation\Container\Attributes\ValidatedFormRequest;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Http\Request;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperties;
use Inertia\ProvidesInertiaProperty;
use Inertia\RenderContext;

class VideoCollectionProperty implements ProvidesInertiaProperties
{
    public function __construct(
        #[CurrentUser] protected ?User $user = null,
        #[RouteParameter('search')] protected ?string $search = null,
        #[FormRequest(VideoIndexRequest::class)] protected mixed $request,
    ) { }

    public function toInertiaProperties(RenderContext $context): array
    {
        dd($this->request);

        return [];

        return [
            'canEdit' => $this->user->can('edit'),
            'canDelete' => $this->user->can('delete'),
            'canPublish' => $this->user->can('publish'),
            'isAdmin' => $this->user->hasRole('admin'),
        ];
    }


}
