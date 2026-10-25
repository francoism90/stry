<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function getConnection(): ?string
    {
        return Config::get('media.delivery.index_connection');
    }

    public function up(): void
    {
        Schema::create('media_indexes', function (Blueprint $table): void {
            $table->id();
            $table->char('version_key', 32);
            $table->string('type', 32);
            $table->string('disk');
            $table->text('path');
            $table->json('data');
            $table->timestamps();

            $table->unique(['version_key', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_indexes');
    }
};
