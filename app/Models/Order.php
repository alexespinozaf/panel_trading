<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
 use HasFactory;

    protected $fillable = [
        'bot_id',
        'bot_run_id',
        'exchange_order_id',
        'client_order_id',
        'symbol',
        'side',
        'type',
        'status',
        'price',
        'avg_price',
        'quantity',
        'executed_quantity',
        'commission',
        'commission_asset',
        'reduce_only',
        'time_in_force',
        'placed_at',
        'updated_at_exchange',
    ];

    protected $casts = [
        'price' => 'decimal:15',
        'avg_price' => 'decimal:15',
        'quantity' => 'decimal:15',
        'executed_quantity' => 'decimal:15',
        'commission' => 'decimal:15',
        'reduce_only' => 'boolean',
        'placed_at' => 'datetime',
        'updated_at_exchange' => 'datetime',
    ];

    public function bot()
    {
        return $this->belongsTo(Bot::class);
    }
}