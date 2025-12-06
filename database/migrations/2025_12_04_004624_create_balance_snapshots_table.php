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
        Schema::create('balance_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exchange_account_id')->constrained()->onDelete('cascade');
            $table->foreignId('bot_run_id')->nullable()->constrained()->nullOnDelete();

            $table->string('asset', 20);
            $table->decimal('free', 30, 15)->default(0);
            $table->decimal('locked', 30, 15)->default(0);
            $table->decimal('total_usd', 30, 10)->nullable();

            $table->timestamp('taken_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balance_snapshots');
    }
};