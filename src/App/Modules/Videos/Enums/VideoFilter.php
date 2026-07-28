<?php

declare(strict_types=1);

namespace App\Modules\Videos\Enums;

enum VideoFilter: string
{
    case Captioned = 'captioned';
    case Shorts = 'shorts';
    case Unseen = 'unseen';
    case Untagged = 'untagged';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'captioned' => __('Captioned'),
            'shorts' => __('Shorts'),
            'unseen' => __('Unseen'),
            'untagged' => __('Untagged'),
        ];
    }
}
