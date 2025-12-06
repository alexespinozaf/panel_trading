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
       Schema::create('bots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('exchange_account_id')->constrained()->onDelete('cascade');
            $table->foreignId('strategy_id')->constrained()->onDelete('cascade');

            $table->string('name');
            $table->string('symbol', 20); // 'BTCUSDT'

            $table->enum('mode', ['live', 'paper'])->default('paper');
            $table->enum('status', ['created', 'running', 'paused', 'stopped', 'error'])
                  ->default('created');

            // Parámetros básicos de riesgo
            $table->decimal('base_order_size', 30, 10)->nullable();   // en quote
            $table->unsignedTinyInteger('leverage')->default(1);
            $table->unsignedInteger('max_positions')->default(1);

            // Config extra de la estrategia aplicada a este bot
            $table->json('config')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bots');
    }
};