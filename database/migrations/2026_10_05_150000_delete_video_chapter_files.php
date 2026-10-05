<?php

declare(strict_types=1);

use Domain\Media\Models\Media;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The chapters WebVTT is served by the direct play stream now, so the stored chapter files
     * are deleted, files included.
     */
    public function up(): void
    {
        Media::query()
            ->where('collection_name', 'chapters')
            ->lazyById()
            ->each(fn (Media $media) => $media->delete());
    }
};
