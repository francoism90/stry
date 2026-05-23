<?php

declare(strict_types=1);

namespace Domain\Videos\Enums;

use Domain\Shared\Contracts\Enumerable;

enum VideoFilter: string implements Enumerable
{
    case Captioned = 'captioned';
    case Unseen = 'unseen';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'captioned' => __('Captioned'),
            'unseen' => __('Unseen'),
        ];
    }
}
