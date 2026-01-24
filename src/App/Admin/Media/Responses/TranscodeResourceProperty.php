<?php

declare(strict_types=1);

namespace App\Admin\Media\Responses;

use App\Api\Media\Resources\TranscodeResource;
use Domain\Media\Models\Media;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

readonly class TranscodeResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Media $media = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ResourceCollection => $this->getCollection());
    }

    protected function getCollection(): ResourceCollection
    {
        return TranscodeResource::collection(
            $this->media?->transcodes ?? []
        );
    }
}
