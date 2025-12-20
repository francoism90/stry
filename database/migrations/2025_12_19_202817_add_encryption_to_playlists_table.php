<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('playlists', function (Blueprint $table) {
            $table->string('encryption_key_id')->nullable()->after('secret_disk')->index();
            $table->text('encryption_key')->nullable()->after('encryption_key_id');
        });
    }

    public function down(): void
    {
        Schema::table('playlists', function (Blueprint $table) {
            $table->dropColumn(['encryption_key_id', 'encryption_key']);
        });
    }
};
