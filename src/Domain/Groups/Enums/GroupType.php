<?php

declare(strict_types=1);

namespace Domain\Groups\Enums;

use Domain\Shared\Contracts\Enumerable;

enum GroupType: string implements Enumerable
{
    case Favorite = 'favorite';
    case Mixer = 'mixer';
    case Saved = 'saved';
    case Viewed = 'viewed';

    public function label(): string
    {
        return match ($this) {
            self::Favorite => __('Favorite'),
            self::Mixer => __('Mixer'),
            self::Saved => __('Saved'),
            self::Viewed => __('Viewed'),
        };
    }

    public static function options(): array
    {
        return array_map(fn (self $type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ], self::cases());
    }
}
