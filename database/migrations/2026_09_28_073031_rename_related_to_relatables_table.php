<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('related', 'relatables');

        DB::table('relatables')->whereNull('score')->update(['score' => 1]);
        DB::table('relatables')->whereNull('boost')->update(['boost' => 1]);

        Schema::table('relatables', function (Blueprint $table) {
            $table->renameColumn('model_type', 'related_type');
            $table->renameColumn('model_id', 'related_id');
            $table->float('score')->default(1)->nullable(false)->change();
            $table->float('boost')->default(1)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('relatables', function (Blueprint $table) {
            $table->renameColumn('related_type', 'model_type');
            $table->renameColumn('related_id', 'model_id');
            $table->float('score')->default(null)->nullable()->change();
            $table->float('boost')->default(null)->nullable()->change();
        });

        Schema::rename('relatables', 'related');
    }
};
