<?php

declare(strict_types=1);

namespace Domain\Users\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum UserScope: string implements Enumerable
{
    use HasLabels;

    case All = 'all';
    case Verified = 'verified';
    case Unverified = 'unverified';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::All => __('All'),
            self::Verified => __('Verified'),
            self::Unverified => __('Unverified'),
            self::Deleted => __('Deleted'),
        };
    }
}
