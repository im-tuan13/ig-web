<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('type', 20)->default('text')->after('message');
            $table->string('sticker_key', 40)->nullable()->after('type');
            $table->index(['conversation_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['conversation_id', 'type']);
            $table->dropColumn(['type', 'sticker_key']);
        });
    }
};
