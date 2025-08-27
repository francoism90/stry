<?php

declare(strict_types=1);

namespace Domain\Shared\Contracts;

use BackedEnum;
use Illuminate\Contracts\Support\Htmlable;

interface Enumerable extends BackedEnum
{
    public function label(): string|Htmlable|null;
}
