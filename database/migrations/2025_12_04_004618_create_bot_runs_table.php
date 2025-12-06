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
       Schema::create('bot_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_id')->constrained()->onDelete('cascade');

            $table->enum('status', ['running', 'finished', 'error', 'stopped'])
                  ->default('running');

            $table->decimal('initial_balance', 30, 10)->nullable();
            $table->decimal('final_balance', 30, 10)->nullable();
            $table->decimal('pnl', 30, 10)->nullable();
            $table->decimal('max_drawdown', 30, 10)->nullable();

            $table->json('stats')->nullable(); // win_rate, trades_count, etc.

            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_runs');
    }
};