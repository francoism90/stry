<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Videos play directly from their clips now, so packaged playlists are no longer needed.
     */
    public function up(): void
    {
        Schema::dropIfExists('playlists');
    }
};
