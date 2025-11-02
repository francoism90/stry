<?php

declare(strict_types=1);

namespace Domain\Users\Concerns;

trait InteractsWithSubscription
{
    public function hasValidSubscription(): bool
    {
        // TODO: Implement subscription validation logic
        return true;
    }
}
