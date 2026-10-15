<?php

declare(strict_types=1);

namespace Modules\Web\Videos\Responses;

use Domain\Media\Models\Media;
use Domain\Shared\DataObjects\StorageDisk;
use Domain\Shared\Enums\DiskContent;
use Domain\Users\Models\User;
use Domain\Videos\Models\Video;
use Foundation\Settings\StorageSettings;
use Inertia\PropertyContext;
use Inertia\ProvidesInertiaProperty;

/**
 * The disks a video can be moved to, for admins, with the disk its clips are on now.
 */
readonly class VideoDisksProperty implements ProvidesInertiaProperty
{
    public function __construct(
        protected Video $video,
        protected ?User $user = null,
    ) {}

    /**
     * @return array<int, array{label: string, value: string, driver: string, current: bool}>
     */
    public function toInertiaProperty(PropertyContext $context): array
    {
        if (! $this->user?->hasRole('super-admin') || $this->user->cannot('update', $this->video)) {
            return [];
        }

        $current = $this->video
            ->getMedia('clips')
            ->map(fn (Media $media): string => $media->disk)
            ->unique()
            ->all();

        return collect(app(StorageSettings::class)->disksFor(DiskContent::Videos))
            ->map(fn (StorageDisk $disk): array => [
                'label' => $disk->label,
                'value' => $disk->name,
                'driver' => $disk->driver->value,
                'current' => in_array($disk->name, $current, true),
            ])
            ->all();
    }
}
