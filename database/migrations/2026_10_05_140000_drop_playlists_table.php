<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Videos play directly from their clips now, so packaged playlists are deleted, files included.
     */
    public function up(): void
    {
        if (! Schema::hasTable('playlists')) {
            return;
        }

        DB::table('playlists')
            ->select(['id', 'disk'])
            ->lazyById()
            ->each(fn (object $playlist) => Storage::disk($playlist->disk)->deleteDirectory((string) $playlist->id));

        Schema::drop('playlists');
    }
};
