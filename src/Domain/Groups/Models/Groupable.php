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

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'groupable_id',
        'groupable_type',
        'options',
        'updated_at',
    ];

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

    public function getScoutKey(): mixed
    {
        return implode('-', [
            $this->group_id,
            $this->groupable_type,
            $this->groupable_id,
        ]);
    }

    public function getScoutKeyName(): string
    {
        return 'group_id';
    }

    public function toSearchableArray(): array
    {
        return [
            'group_id' => $this->group_id,
            'groupable_type' => $this->groupable_type,
            'groupable_id' => $this->groupable_id,
            'options' => $this->options,
            'created_at' => $this->created_at->getTimestamp(),
            'updated_at' => $this->updated_at->getTimestamp(),
        ];
    }
}
