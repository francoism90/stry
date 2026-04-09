<?php

declare(strict_types=1);

namespace Domain\Shared\Contracts;

use BackedEnum;

interface Enumerable extends BackedEnum
{
    /** @return array<string, string> */
    public static function labels(): array;
}
