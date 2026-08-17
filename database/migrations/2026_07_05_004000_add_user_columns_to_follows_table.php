<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('follows', function (Blueprint $table) {
            if (! Schema::hasColumn('follows', 'follower_id')) {
                $table->foreignId('follower_id')->after('id')->constrained('users')->cascadeOnDelete();
            }

            if (! Schema::hasColumn('follows', 'following_id')) {
                $table->foreignId('following_id')->after('follower_id')->constrained('users')->cascadeOnDelete();
            }
        });

        Schema::table('follows', function (Blueprint $table) {
            $table->unique(['follower_id', 'following_id']);
        });
    }

    public function down(): void
    {
        Schema::table('follows', function (Blueprint $table) {
            $table->dropUnique(['follower_id', 'following_id']);
            $table->dropConstrainedForeignId('following_id');
            $table->dropConstrainedForeignId('follower_id');
        });
    }
};
