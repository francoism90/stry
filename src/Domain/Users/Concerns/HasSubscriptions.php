<?php

declare(strict_types=1);

namespace Domain\Users\Concerns;

trait HasSubscriptions
{
    public function hasValidSubscription(): bool
    {
        return true;
    }
}
