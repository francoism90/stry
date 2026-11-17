<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum VideoScope: string implements Enumerable
{
    use HasLabels;

    case All = 'all';
    case Watching = 'watching';
    case Shorts = 'shorts';
    case Unseen = 'unseen';
    case Untagged = 'untagged';
    case Captioned = 'captions';

    public function label(): string
    {
        return match ($this) {
            self::All => __('All'),
            self::Watching => __('In progress'),
            self::Shorts => __('Shorts'),
            self::Unseen => __('Unseen'),
            self::Untagged => __('Untagged'),
            self::Captioned => __('Captioned'),
        };
    }
}
