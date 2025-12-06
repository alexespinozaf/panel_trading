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
         Schema::create('candles', function (Blueprint $table) {
            $table->id();
            $table->string('symbol', 20);
            $table->string('timeframe', 10); // '1m', '5m', '15m', etc.

            $table->timestamp('open_time'); // tiempo de la vela
            $table->decimal('open', 30, 15);
            $table->decimal('high', 30, 15);
            $table->decimal('low', 30, 15);
            $table->decimal('close', 30, 15);
            $table->decimal('volume', 30, 15)->nullable();

            $table->unique(['symbol', 'timeframe', 'open_time']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candles');
    }
};
