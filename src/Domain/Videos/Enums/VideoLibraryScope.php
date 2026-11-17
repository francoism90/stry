<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum VideoLibraryScope: string implements Enumerable
{
    use HasLabels;

    case All = 'all';
    case Verified = 'verified';
    case Pending = 'pending';
    case Failed = 'failed';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::All => __('All'),
            self::Verified => __('Verified'),
            self::Pending => __('Pending'),
            self::Failed => __('Failed'),
            self::Deleted => __('Deleted'),
        };
    }
}
