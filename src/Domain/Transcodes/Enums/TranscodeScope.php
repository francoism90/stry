<?php

declare(strict_types=1);

namespace Domain\Transcodes\Enums;

use Domain\Shared\Contracts\Enumerable;

enum TranscodeScope: string implements Enumerable
{
    case All = 'all';
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'all' => __('All'),
            'pending' => __('Pending'),
            'processing' => __('Processing'),
            'completed' => __('Completed'),
            'failed' => __('Failed'),
        ];
    }
}
