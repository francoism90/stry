<?php

declare(strict_types=1);

namespace Domain\Shared\Concerns;

use Illuminate\Database\Eloquent\BroadcastsEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Broadcasts model events as "{model}.{event}" on the "{models}.{ulid}" channel.
 *
 * @mixin Model
 */
trait BroadcastsModelEvents
{
    use BroadcastsEvents;

    public function broadcastChannel(): string
    {
        return Str::plural($this->broadcastName()).'.'.$this->getRouteKey();
    }

    public function broadcastAs(string $event): string
    {
        return "{$this->broadcastName()}.{$event}";
    }

    public function broadcastWith(string $event): array
    {
        return ['id' => $this->getRouteKey()];
    }

    public function broadcastAfterCommit(): bool
    {
        return true;
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }

    protected function broadcastName(): string
    {
        return Str::snake(class_basename(static::class));
    }
}
