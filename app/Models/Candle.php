<?php
// app/Models/Candle.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candle extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'timeframe',
        'open_time',
        'open',
        'high',
        'low',
        'close',
        'volume',
    ];

    protected $casts = [
        'open_time' => 'datetime',
        'open' => 'decimal:15',
        'high' => 'decimal:15',
        'low' => 'decimal:15',
        'close' => 'decimal:15',
        'volume' => 'decimal:15',
    ];
}
