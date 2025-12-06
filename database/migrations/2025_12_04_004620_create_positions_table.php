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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_id')->constrained()->onDelete('cascade');

            $table->string('symbol', 20);
            $table->enum('side', ['LONG', 'SHORT']);

            $table->decimal('quantity', 30, 15);
            $table->decimal('entry_price', 30, 15);
            $table->decimal('liquidation_price', 30, 15)->nullable();

            $table->decimal('take_profit', 30, 15)->nullable();
            $table->decimal('stop_loss', 30, 15)->nullable();

            $table->enum('status', ['OPEN', 'CLOSED', 'LIQUIDATED'])
                  ->default('OPEN');

            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};