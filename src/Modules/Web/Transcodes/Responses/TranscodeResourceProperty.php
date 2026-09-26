<?php

declare(strict_types=1);

namespace Modules\Web\Transcodes\Responses;

use Domain\Transcodes\Models\Transcode;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;
use Modules\Api\Transcodes\Resources\TranscodeResource;

readonly class TranscodeResourceProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected ?Transcode $transcode = null,
    ) {}

    public function toInertiaProperty(PropertyContext $context): mixed
    {
        return once(fn (): ?TranscodeResource => $this->getResource());
    }

    protected function getResource(): ?TranscodeResource
    {
        if (! $this->transcode) {
            return null;
        }

        return TranscodeResource::make($this->transcode->loadMissing('transcodable'));
    }
}
