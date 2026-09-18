<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caller_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 10)->default('video');
            $table->string('status', 20)->default('calling');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedInteger('duration')->nullable();
            $table->timestamps();
            $table->index(['receiver_id', 'status']);
            $table->index(['caller_id', 'status']);
        });

        Schema::create('call_signals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('call_id')->constrained('calls')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->string('kind', 20);
            $table->json('payload');
            $table->timestamps();
            $table->index(['call_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_signals');
        Schema::dropIfExists('calls');
    }
};
