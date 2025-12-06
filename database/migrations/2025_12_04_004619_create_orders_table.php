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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_id')->constrained()->onDelete('cascade');
            $table->foreignId('bot_run_id')->nullable()->constrained()->nullOnDelete();

            $table->string('exchange_order_id')->nullable();
            $table->string('client_order_id')->nullable();

            $table->string('symbol', 20);
            $table->enum('side', ['BUY', 'SELL']);
            $table->enum('type', ['MARKET', 'LIMIT', 'STOP', 'STOP_MARKET', 'TAKE_PROFIT', 'TAKE_PROFIT_MARKET'])
                  ->default('LIMIT');

            $table->enum('status', [
                'NEW',
                'PARTIALLY_FILLED',
                'FILLED',
                'CANCELED',
                'REJECTED',
                'EXPIRED'
            ])->default('NEW');

            $table->decimal('price', 30, 15)->nullable();
            $table->decimal('avg_price', 30, 15)->nullable();
            $table->decimal('quantity', 30, 15);
            $table->decimal('executed_quantity', 30, 15)->default(0);

            $table->decimal('commission', 30, 15)->nullable();
            $table->string('commission_asset', 20)->nullable();

            $table->boolean('reduce_only')->default(false);
            $table->string('time_in_force', 10)->nullable();

            $table->timestamp('placed_at')->nullable();
            $table->timestamp('updated_at_exchange')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};