<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('strategies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type')->nullable(); // 'rsi_mean_reversion', etc.
            $table->text('description')->nullable();
            // Config genérico en JSON: timeframes, parámetros, etc.
            $table->json('config')->nullable();

            $table->boolean('is_public')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('strategies');
    }
};