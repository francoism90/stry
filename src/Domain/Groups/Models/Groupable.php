<?php

declare(strict_types=1);

namespace Domain\Groups\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Laravel\Scout\Searchable;

class Groupable extends MorphPivot
{
    use Searchable;

    /**
     * @var string
     */
    protected $table = 'groupables';

    protected function casts(): array
    {
        return [
            'options' => AsArrayObject::class,
        ];
    }

    public function groupable(): MorphTo
    {
        return $this->MorphTo();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->getScoutKey(),
            'group_id' => (int) $this->group_id,
            'groupable_id' => (int) $this->groupable_id,
            'groupable_type' => (string) $this->groupable_type,
            'created_at' => (int) $this->created_at->getTimestamp(),
            'updated_at' => (int) $this->updated_at->getTimestamp(),
        ];
    }
}
