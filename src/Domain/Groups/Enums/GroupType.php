<?php

declare(strict_types=1);

namespace Domain\Groups\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum GroupType: string implements Enumerable
{
    use HasLabels;

    case Custom = 'custom';
    case Liked = 'liked';
    case Mixer = 'mixer';
    case Saved = 'saved';
    case Viewed = 'viewed';

    public function label(): string
    {
        return match ($this) {
            self::Custom => __('Custom'),
            self::Liked => __('Liked'),
            self::Mixer => __('Mixer'),
            self::Saved => __('Saved'),
            self::Viewed => __('Viewed'),
        };
    }

    public function priority(): int
    {
        return match ($this) {
            self::Liked => 0,
            self::Saved => 1,
            self::Viewed => 2,
            self::Custom => 3,
            self::Mixer => 4,
        };
    }
}
