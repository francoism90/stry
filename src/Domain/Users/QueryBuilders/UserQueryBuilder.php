<?php

declare(strict_types=1);

namespace Domain\Users\QueryBuilders;

use Domain\Users\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * @template TModel of User
 *
 * @extends Builder<TModel>
 */
class UserQueryBuilder extends Builder
{
    //
}
