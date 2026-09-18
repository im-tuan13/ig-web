<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blocker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['blocker_id', 'blocked_id']);
            $table->index('blocked_id');
        });

        Schema::create('user_mutes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('muter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('muted_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['muter_id', 'muted_id']);
            $table->index('muted_id');
        });

        Schema::create('reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('reportable');
            $table->string('reason', 80);
            $table->text('details')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('user_mutes');
        Schema::dropIfExists('user_blocks');
    }
};
