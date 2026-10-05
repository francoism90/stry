<?php

declare(strict_types=1);

use Domain\Media\Models\Media;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Renditions are encoded while they're watched now, so renditions encoded ahead next to clips
     * are deleted, files included.
     */
    public function up(): void
    {
        Media::query()
            ->where('collection_name', 'clips')
            ->whereNotNull('custom_properties->renditions')
            ->lazyById()
            ->each(function (Media $clip): void {
                Storage::disk($clip->disk)->delete(array_values(array_map(strval(...), (array) $clip->getCustomProperty('renditions.paths', []))));

                $clip->forgetCustomProperty('renditions')->saveQuietly();
            });
    }
};
