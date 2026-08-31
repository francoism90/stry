<?php

declare(strict_types=1);

namespace Domain\Shared\Enums;

use Domain\Shared\Contracts\Enumerable;

enum Locale: string implements Enumerable
{
    case EnUs = 'en-US';
    case NlNl = 'nl-NL';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'en-US' => __('English (US)'),
            'nl-NL' => __('Dutch (Netherlands)'),
        ];
    }
}
