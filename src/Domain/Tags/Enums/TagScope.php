<?php

declare(strict_types=1);

namespace Domain\Tags\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum TagScope: string implements Enumerable
{
    use HasLabels;

    case All = 'all';
    case Serie = 'serie';
    case Studio = 'studio';
    case Genre = 'genre';
    case Person = 'person';
    case Language = 'language';

    public function label(): string
    {
        return match ($this) {
            self::All => __('All'),
            self::Serie => __('Serie'),
            self::Studio => __('Studio'),
            self::Genre => __('Genre'),
            self::Person => __('Person'),
            self::Language => __('Language'),
        };
    }
}
