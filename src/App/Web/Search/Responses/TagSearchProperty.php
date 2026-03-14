<?php

declare(strict_types=1);

namespace App\Web\Search\Responses;

use App\Api\Tags\Resources\TagResource;
use Domain\Tags\Models\Tag;
use Domain\Tags\Scopes\TagFilterScope;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class TagSearchProperty implements ProvidesInertiaProperty
{
    public function __construct(protected string $query = '', protected int $limit = 6) {}

    public function toInertiaProperty(PropertyContext $context): ResourceCollection
    {
        return TagResource::collection(
            Tag::search($this->query)
                ->tap(new TagFilterScope)
                ->take($this->limit)
                ->get()
        );
    }
}
