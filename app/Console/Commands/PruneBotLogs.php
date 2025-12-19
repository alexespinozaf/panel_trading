<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BotLog;
use Carbon\Carbon;

class PruneBotLogs extends Command
{
    protected $signature = 'bot-logs:prune {--days=90}';
    protected $description = 'Elimina registros antiguos de bot_logs';

    public function handle()
    {
        $days = (int) $this->option('days');
        $before = Carbon::now()->subDays($days);

        $deleted = BotLog::query()
            ->where('created_at', '<', $before)
            ->delete();

        $this->info("Eliminados {$deleted} bot_logs más antiguos que {$days} días.");
    }
}
