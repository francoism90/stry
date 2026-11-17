<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum TagSorter: string implements Enumerable
{
    use HasLabels;

    case Videos = 'videos';
    case Newest = 'newest';
    case Oldest = 'oldest';

    public function label(): string
    {
        return match ($this) {
            self::Videos => __('Videos'),
            self::Newest => __('Newest'),
            self::Oldest => __('Oldest'),
        };
    }
}
