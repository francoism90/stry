<?php

declare(strict_types=1);

namespace Domain\Media\Collections;

use Domain\Media\Models\Media;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection as Collection;

/**
 * @extends Collection<int, Media>
 */
class MediaCollection extends Collection
{
    //
}
