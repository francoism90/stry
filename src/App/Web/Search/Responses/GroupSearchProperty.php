<?php

declare(strict_types=1);

namespace App\Web\Search\Responses;

use App\Api\Groups\Resources\GroupResource;
use Domain\Groups\Models\Group;
use Domain\Groups\Scopes\GroupFilterScope;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class GroupSearchProperty implements ProvidesInertiaProperty
{
    public function __construct(protected string $query = '', protected int $limit = 6) {}

    public function toInertiaProperty(PropertyContext $context): ResourceCollection
    {
        return GroupResource::collection(
            Group::search($this->query)
                ->tap(new GroupFilterScope)
                ->take($this->limit)
                ->get()
        );
    }
}
