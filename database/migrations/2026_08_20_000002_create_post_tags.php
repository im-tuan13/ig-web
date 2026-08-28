<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('post_tags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('x_position', 5, 2);
            $table->decimal('y_position', 5, 2);
            $table->timestamps();
            $table->unique(['post_id', 'user_id']);
        });
    }

    public function down(): void { Schema::dropIfExists('post_tags'); }
};
