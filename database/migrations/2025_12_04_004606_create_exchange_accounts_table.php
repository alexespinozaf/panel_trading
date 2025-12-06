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
        Schema::create('exchange_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('exchange', 50); // 'binance', 'bybit', etc.
            $table->string('name');         // alias: "Binance principal"

            // API keys SIEMPRE cifradas con Crypt
            $table->text('api_key_encrypted');
            $table->text('api_secret_encrypted');

            $table->boolean('is_futures')->default(false);
            $table->enum('status', ['active', 'disabled', 'error'])
                  ->default('active');

            $table->json('extra')->nullable(); // para cosas tipo subaccount_id, etc.
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_accounts');
    }
};
