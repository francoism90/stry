<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->nullableMorphs('model');
            $table->jsonb('name');
            $table->jsonb('content')->nullable();
            $table->jsonb('summary')->nullable();
            $table->jsonb('titles')->nullable();
            $table->string('season')->nullable()->index();
            $table->string('episode')->nullable()->index();
            $table->string('part')->nullable()->index();
            $table->boolean('adult')->default(false)->index();
            $table->float('snapshot')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
