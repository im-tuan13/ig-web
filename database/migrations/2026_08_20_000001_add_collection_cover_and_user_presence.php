<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('collections', fn (Blueprint $table) => $table->string('cover_image')->nullable()->after('name'));
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_online')->default(false)->after('is_private');
            $table->timestamp('last_seen_at')->nullable()->after('is_online');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['is_online', 'last_seen_at']);
        });
        Schema::table('collections', fn (Blueprint $table) => $table->dropColumn('cover_image'));
    }
};
