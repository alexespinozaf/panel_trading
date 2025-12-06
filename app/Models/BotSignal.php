<?php
// app/Models/BotSignal.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotSignal extends Model
{
    use HasFactory;

    protected $fillable = [
        'bot_id',
        'symbol',
        'timeframe',
        'signal_time',
        'signal',
        'price',
        'rsi',
        'ema_fast',
        'ema_slow',
        'atr',
        'future_return',
    ];

    protected $casts = [
        'signal_time' => 'datetime',
        'price' => 'decimal:15',
        'rsi' => 'decimal:5',
        'ema_fast' => 'decimal:15',
        'ema_slow' => 'decimal:15',
        'atr' => 'decimal:15',
        'future_return' => 'decimal:5',
    ];

    public function bot()
    {
        return $this->belongsTo(Bot::class);
    }
}
