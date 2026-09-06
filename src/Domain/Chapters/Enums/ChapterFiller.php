<?php

declare(strict_types=1);

namespace Domain\Chapters\Enums;

use Domain\Shared\Contracts\Enumerable;

enum ChapterFiller: string implements Enumerable
{
    case MainEvent = 'main_event';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'main_event' => __('Main Event'),
        ];
    }
}
