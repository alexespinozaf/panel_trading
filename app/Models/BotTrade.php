<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotTrade extends Model
{
    protected $table = 'bot_trades';

    protected $fillable = [
        'bot_id',
        'order_id',
        'symbol',
        'side',
        'price',
        'quantity',
        'fee',
        'fee_asset',
        'realized_pnl',
        'trade_time',
    ];

    protected $casts = [
        'price' => 'decimal:15',
        'quantity' => 'decimal:15',
        'fee' => 'decimal:15',
        'realized_pnl' => 'decimal:10',
        'trade_time' => 'datetime',
    ];

    public function bot()
    {
        return $this->belongsTo(Bot::class);
    }
}
