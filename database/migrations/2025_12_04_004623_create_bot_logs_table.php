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
    Schema::create('bot_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bot_id')->constrained()->onDelete('cascade');
            $table->foreignId('bot_run_id')->nullable()->constrained()->nullOnDelete();

            $table->string('level', 20)->default('info'); // info, warning, error, debug
            $table->string('code', 50)->nullable();       // ej: ORDER_REJECTED

            $table->text('message');
            $table->json('context')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bot_logs');
    }
};