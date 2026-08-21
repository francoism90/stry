<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoScope: string implements Enumerable
{
    case All = 'all';
    case Shorts = 'shorts';
    case Unseen = 'unseen';
    case Untagged = 'untagged';
    case Captioned = 'captions';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'all' => __('All'),
            'shorts' => __('Shorts'),
            'unseen' => __('Unseen'),
            'untagged' => __('Untagged'),
            'captions' => __('Captioned'),
        ];
    }
}
