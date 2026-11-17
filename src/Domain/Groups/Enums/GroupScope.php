<?php

declare(strict_types=1);

namespace Domain\Groups\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum GroupScope: string implements Enumerable
{
    use HasLabels;

    case All = 'all';
    case Custom = 'custom';
    case Mixer = 'mixer';

    public function label(): string
    {
        return match ($this) {
            self::All => __('All'),
            self::Custom => __('Custom'),
            self::Mixer => __('Mixer'),
        };
    }
}
