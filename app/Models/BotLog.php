<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotLog extends Model
{
 use HasFactory;

    protected $fillable = [
        'bot_id',
        'bot_run_id',
        'level',
        'code',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function bot()
    {
        return $this->belongsTo(Bot::class);
    }
}