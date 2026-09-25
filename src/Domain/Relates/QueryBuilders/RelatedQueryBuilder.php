<?php

declare(strict_types=1);

namespace Domain\Relates\QueryBuilders;

use Domain\Relates\Models\Related;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of Related
 *
 * @extends Builder<TModel>
 */
class RelatedQueryBuilder extends Builder
{
    //
}
