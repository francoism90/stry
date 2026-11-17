<?php

declare(strict_types=1);

namespace Domain\Users\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum UserSorter: string implements Enumerable
{
    use HasLabels;

    case Newest = 'newest';
    case Oldest = 'oldest';

    public function label(): string
    {
        return match ($this) {
            self::Newest => __('Newest'),
            self::Oldest => __('Oldest'),
        };
    }
}
