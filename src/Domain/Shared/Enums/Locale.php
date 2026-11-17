<?php

declare(strict_types=1);

namespace Domain\Shared\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum Locale: string implements Enumerable
{
    use HasLabels;

    case EnUs = 'en-US';
    case NlNl = 'nl-NL';

    public function label(): string
    {
        return match ($this) {
            self::EnUs => __('English (US)'),
            self::NlNl => __('Dutch (Netherlands)'),
        };
    }
}
