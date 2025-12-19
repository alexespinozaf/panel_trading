<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $table = 'positions';

    protected $fillable = [
        'bot_id',
        'symbol',
        'side',
        'quantity',
        'entry_price',
        'liquidation_price',
        'take_profit',
        'stop_loss',
        'status',
        'opened_at',
        'closed_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:15',
        'entry_price' => 'decimal:15',
        'liquidation_price' => 'decimal:15',
        'take_profit' => 'decimal:15',
        'stop_loss' => 'decimal:15',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function bot()
    {
        return $this->belongsTo(Bot::class);
    }
}
