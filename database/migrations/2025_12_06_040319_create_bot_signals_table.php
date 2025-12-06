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
     Schema::create('bot_signals', function (Blueprint $table) {
    $table->id();
    $table->foreignId('bot_id')->constrained()->onDelete('cascade');
    $table->string('symbol', 20);
    $table->string('timeframe', 10);
    $table->timestamp('signal_time');
    $table->enum('signal', ['BUY', 'SELL', 'NONE']);
    $table->decimal('price', 30, 15);

    // features básicos que la IA podrá usar (snapshot)
    $table->decimal('rsi', 10, 5)->nullable();
    $table->decimal('ema_fast', 30, 15)->nullable();
    $table->decimal('ema_slow', 30, 15)->nullable();
    $table->decimal('atr', 30, 15)->nullable();

    // label que llenaremos después con un script offline:
    // retorno % en las próximas N velas
    $table->decimal('future_return', 10, 5)->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bot_signals', function (Blueprint $table) {
               Schema::dropIfExists('bot_signals');

        });
    }
};
