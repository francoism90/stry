<?php

declare(strict_types=1);

namespace App\Modules\Transcodes\Responses;

use App\Modules\Transcodes\Resources\TranscodeResource;
use Domain\Transcodes\Models\Transcode;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

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

        return $this
            ->transcode
            ->loadMissing('transcodable')
            ->toResource(TranscodeResource::class);
    }
}
