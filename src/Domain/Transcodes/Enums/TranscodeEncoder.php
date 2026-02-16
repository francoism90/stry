<?php

declare(strict_types=1);

namespace Domain\Transcodes\Enums;

use Domain\Shared\Contracts\Enumerable;

enum TranscodeEncoder: string implements Enumerable
{
    case AV1 = 'av1';

    public function label(): string
    {
        return match ($this) {
            self::AV1 => 'ab-av1 (AV1)',
        };
    }

    public static function options(): array
    {
        return array_map(fn (self $item) => [
            'value' => $item->value,
            'label' => $item->label(),
        ], self::cases());
    }
}
