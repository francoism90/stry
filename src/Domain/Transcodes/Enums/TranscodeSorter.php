<?php

declare(strict_types=1);

namespace Domain\Transcodes\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum TranscodeSorter: string implements Enumerable
{
    use HasLabels;

    case Newest = 'newest';
    case Oldest = 'oldest';
    case Updated = 'updated';

    public function label(): string
    {
        return match ($this) {
            self::Newest => __('Newest'),
            self::Oldest => __('Oldest'),
            self::Updated => __('Recently updated'),
        };
    }
}
