<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('related', function (Blueprint $table) {
            $table->id();
            $table->morphs('relatable');
            $table->morphs('model');
            $table->float('score')->nullable();
            $table->float('boost')->nullable();
            $table->jsonb('options')->nullable();
            $table->timestamps();
            $table->unique(['relatable_id', 'relatable_type', 'model_id', 'model_type']);
            $table->index(['relatable_id', 'relatable_type']);
            $table->index(['model_id', 'model_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('related');
    }
};
