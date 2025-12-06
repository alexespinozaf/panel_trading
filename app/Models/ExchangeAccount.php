<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExchangeAccount extends Model
{
 use HasFactory;

    protected $fillable = [
        'user_id',
        'exchange',
        'name',
        'api_key_encrypted',
        'api_secret_encrypted',
        'is_futures',
            'is_testnet',       
        'status',
        'extra',
        'last_synced_at',
    ];

    protected $casts = [
        'extra' => 'array',
        'is_futures' => 'boolean',
        'is_testnet' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bots()
    {
        return $this->hasMany(Bot::class);
    }
}
