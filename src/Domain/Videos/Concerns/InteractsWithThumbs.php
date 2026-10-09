<?php

declare(strict_types=1);

namespace Domain\Videos\Concerns;

use Domain\Profiles\Models\Profile;
use Domain\Videos\Models\Video;
use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @template TPivotModel of MorphPivot
 */
trait InteractsWithThumbs
{
    /**
     * The videos the thumbnail video is picked from, ordered so the one to use comes first.
     *
     * @return MorphToMany<Video, $this, TPivotModel>
     */
    abstract protected function thumbnailCandidates(): MorphToMany;

    /**
     * The first candidate with a clip that the current profile may see, used as the model's picture.
     */
    public function thumbnailVideo(): ?Video
    {
        if ($this->relationLoaded('thumbs')) {
            return $this->thumbs->first();
        }

        return $this->thumbs()->first();
    }

    /**
     * Holds at most the thumbnail video, so a list of models can eager load all of them in one query.
     *
     * @return MorphToMany<Video, $this, TPivotModel>
     */
    public function thumbs(): MorphToMany
    {
        return $this->thumbnailCandidates()
            ->forProfile(Profile::current())
            ->withClips()
            ->with('media')
            ->limit(1);
    }
}
