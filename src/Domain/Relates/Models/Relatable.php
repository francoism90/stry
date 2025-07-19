<?php

declare(strict_types=1);

namespace Domain\Relates\Models;

use Domain\Relates\Collections\RelatedCollection;
use Domain\Relates\QueryBuilders\RelatedQueryBuilder;
use Domain\Relates\Scopes\ScoreScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ScopedBy(ScoreScope::class)]
class Relatable extends Model
{
    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'relate_type',
        'relate_id',
        'model_id',
        'model_type',
        'score',
        'boost',
        'options',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'boost' => 'decimal:2',
            'options' => AsArrayObject::class,
        ];
    }

    public function newEloquentBuilder($query): RelatedQueryBuilder
    {
        return new RelatedQueryBuilder($query);
    }

    public function newCollection(array $models = []): RelatedCollection
    {
        return new RelatedCollection($models);
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function relate(): MorphTo
    {
        return $this->morphTo();
    }

    public function getRelatedValues(): array
    {
        return [
            'type' => $this->relate_type,
            'id' => $this->relate_id,
        ];
    }
}
