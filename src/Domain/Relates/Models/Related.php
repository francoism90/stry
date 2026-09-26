<?php

declare(strict_types=1);

namespace Domain\Relates\Models;

use Domain\Relates\Collections\RelatedCollection;
use Domain\Relates\QueryBuilders\RelatedQueryBuilder;
use Illuminate\Database\Eloquent\Attributes\CollectedBy;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[CollectedBy(RelatedCollection::class)]
#[UseEloquentBuilder(RelatedQueryBuilder::class)]
class Related extends Model
{
    /**
     * @var string
     */
    protected $table = 'related';

    /**
     * @var list<string>
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

    /**
     * @return MorphTo<Model, $this>
     */
    public function relatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }
}
