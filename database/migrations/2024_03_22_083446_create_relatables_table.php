<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relatables', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->morphs('relate');
            $table->float('score')->nullable();
            $table->float('boost')->nullable();
            $table->jsonb('options')->nullable();
            $table->timestamps();
            $table->unique(['model_id', 'model_type', 'relate_id', 'relate_type']);
            $table->index(['model_id', 'model_type']);
            $table->index(['relate_id', 'relate_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relatables');
    }
};
