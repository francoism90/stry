<?php

declare(strict_types=1);

namespace App\Api\Chapters\Resources;

use Domain\Chapters\Models\Chapter;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Chapter
 */
class ChapterResource extends JsonResource
{
    /**
     * @var bool
     */
    public $preserveKeys = true;

    public function toArray($request): array
    {
        return [
            'id' => $this->getRouteKey(),
            'type' => $this->type,
            'label' => $this->label,
            'start_time' => (float) $this->start_time,
            'end_time' => (float) $this->end_time,
            'sort' => $this->sort,
            'skippable' => $this->type->isSkippable(),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
