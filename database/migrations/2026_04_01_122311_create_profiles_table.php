<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table
                ->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('name');
            $table->string('avatar')->nullable();
            $table->boolean('is_kids')->default(false)->index();
            $table->boolean('is_primary')->default(false)->index();
            $table->string('state')->index();
            $table->jsonb('settings')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
