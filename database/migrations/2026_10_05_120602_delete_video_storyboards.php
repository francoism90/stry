<?php

declare(strict_types=1);

use Domain\Media\Models\Media;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Seek previews come from the clip's thumbnails now, so the storyboard sprites and their
     * WebVTT files are deleted, files included.
     */
    public function up(): void
    {
        Media::query()
            ->where('collection_name', 'storyboards')
            ->lazyById()
            ->each(fn (Media $media) => $media->delete());
    }
};
