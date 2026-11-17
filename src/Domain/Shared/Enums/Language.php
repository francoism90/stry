<?php

declare(strict_types=1);

namespace Domain\Shared\Enums;

use Foxws\Essentials\Concerns\HasLabels;
use Foxws\Essentials\Contracts\Enumerable;

enum Language: string implements Enumerable
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
