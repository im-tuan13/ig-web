<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void { Schema::create('notes', function (Blueprint $table): void { $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete(); $table->string('content',60); $table->timestamp('expires_at'); $table->timestamps(); $table->index(['user_id','expires_at']); }); }
 public function down(): void { Schema::dropIfExists('notes'); }
};
