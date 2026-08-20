<?php

declare(strict_types=1);

namespace App\Web\Search\Responses;

use App\Api\Groups\Resources\GroupResource;
use Domain\Groups\Models\Group;
use Domain\Groups\QueryBuilders\GroupQueryBuilder;
use Foxws\ScoutBuilder\ScoutBuilder;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class GroupSearchProperty implements ProvidesInertiaProperty
{
    public function __construct(protected string $query = '', protected int $limit = 6) {}

    public function toInertiaProperty(PropertyContext $context): ResourceCollection
    {
        return GroupResource::collection(
            ScoutBuilder::for(Group::search($this->query))
                ->query(fn (GroupQueryBuilder $builder) => $builder->withCount('groupables'))
                ->take($this->limit)
                ->get()
        );
    }
}
