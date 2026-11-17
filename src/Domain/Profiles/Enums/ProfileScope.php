<?php

declare(strict_types=1);

namespace Domain\Profiles\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum ProfileScope: string implements Enumerable
{
    use HasLabels;

    case All = 'all';
    case Kids = 'kids';
    case Primary = 'primary';

    public function label(): string
    {
        return match ($this) {
            self::All => __('All'),
            self::Kids => __('Kids'),
            self::Primary => __('Primary'),
        };
    }
}
