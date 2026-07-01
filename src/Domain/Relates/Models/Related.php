<?php

declare(strict_types=1);

namespace Domain\Relates\Models;

use Domain\Relates\Collections\RelatedCollection;
use Domain\Relates\QueryBuilders\RelatedQueryBuilder;
use Domain\Shared\Concerns\InteractsWithCache;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Related extends Model
{
    use InteractsWithCache;

    /**
     * @var string
     */
    protected $table = 'related';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'relatable_type',
        'relatable_id',
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

    public function relatable(): MorphTo
    {
        return $this->morphTo();
    }

    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
