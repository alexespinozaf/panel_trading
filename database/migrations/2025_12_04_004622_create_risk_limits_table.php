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
        Schema::create('risk_limits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('exchange_account_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('bot_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('max_daily_loss', 30, 10)->nullable();       // en USD
            $table->unsignedInteger('max_daily_trades')->nullable();
            $table->decimal('max_position_size_usd', 30, 10)->nullable();
            $table->unsignedTinyInteger('max_leverage')->nullable();

            $table->boolean('circuit_breaker_enabled')->default(true);

            $table->json('params')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_limits');
    }
};