<?php

// app/Models/Bot.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bot extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exchange_account_id',
        'strategy_id',
        'name',
        'symbol',
        'mode',
        'status',
        'base_order_size',
        'leverage',
        'max_positions',
        'config',
        'started_at',
        'stopped_at',
    ];

    protected $casts = [
        'config' => 'array',
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function exchangeAccount()
    {
        return $this->belongsTo(ExchangeAccount::class);
    }

    public function strategy()
    {
        return $this->belongsTo(Strategy::class);
    }

    public function runs()
    {
        return $this->hasMany(BotRun::class);
    }
    public function signals()
{
    return $this->hasMany(BotSignal::class);
}
}
