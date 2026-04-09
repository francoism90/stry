<?php

declare(strict_types=1);

namespace Domain\Shared\Contracts;

use BackedEnum;

interface Enumerable extends BackedEnum
{
    public function label(): string;

    /** @return array<string, string> */
    public static function labels(): array;
}
