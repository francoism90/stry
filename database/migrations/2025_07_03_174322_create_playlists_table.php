<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playlists', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table
                ->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->nullableMorphs('playlistable');
            $table->string('disk');
            $table->string('file_name');
            $table->string('secret_disk')->nullable();
            $table->jsonb('progress')->nullable();
            $table->string('collection')->nullable()->index();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('transcoded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playlists');
    }
};
