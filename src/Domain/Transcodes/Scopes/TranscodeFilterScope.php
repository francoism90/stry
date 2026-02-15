<?php

declare(strict_types=1);

namespace Domain\Transcodes\Scopes;

use Domain\Transcodes\Enums\TranscodeEncoder;
use Illuminate\Database\Eloquent\Builder;

readonly class TranscodeFilterScope
{
    public function __construct(
        public TranscodeEncoder|string|null $encoder = null,
    ) {}

    public function __invoke(Builder $query): void
    {
        $query
            ->when($this->getEncoder(), fn (Builder $query, TranscodeEncoder $encoder) => $query->encoder($encoder))
            ->latest();
    }

    protected function getEncoder(): ?TranscodeEncoder
    {
        $encoderValue = $this->encoder ?? null;

        return is_string($encoderValue) ? TranscodeEncoder::tryFrom($encoderValue) : $encoderValue;
    }
}
