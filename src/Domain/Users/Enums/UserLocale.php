<?php

declare(strict_types=1);

namespace Domain\Users\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum UserLocale: string implements Enumerable
{
    use HasLabels;

    case English = 'en';
    case Dutch = 'nl';

    public function label(): string
    {
        return match ($this) {
            self::English => __('English'),
            self::Dutch => __('Dutch'),
        };
    }
}
