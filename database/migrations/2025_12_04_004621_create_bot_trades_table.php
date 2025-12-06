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
        Schema::create('bot_trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_id')->constrained()->onDelete('cascade');
            $table->foreignId('bot_run_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();

            $table->string('symbol', 20);
            $table->enum('side', ['BUY', 'SELL']);

            $table->decimal('price', 30, 15);
            $table->decimal('quantity', 30, 15);

            $table->decimal('fee', 30, 15)->nullable();
            $table->string('fee_asset', 20)->nullable();

            $table->decimal('realized_pnl', 30, 10)->default(0);

            $table->timestamp('trade_time')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_trades');
    }
};