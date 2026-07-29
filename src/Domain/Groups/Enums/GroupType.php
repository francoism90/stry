<?php

declare(strict_types=1);

namespace Domain\Groups\Enums;

enum GroupType: string
{
    case Custom = 'custom';
    case Liked = 'liked';
    case Mixer = 'mixer';
    case Saved = 'saved';
    case Viewed = 'viewed';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'custom' => __('Custom'),
            'liked' => __('Liked'),
            'mixer' => __('Mixer'),
            'saved' => __('Saved'),
            'viewed' => __('Viewed'),
        ];
    }
}
