<?php

declare(strict_types=1);

namespace Modules\Web\Shuffle\Enums;

enum ShuffleType: string
{
    case Videos = 'videos';
    case Tags = 'tags';
}
