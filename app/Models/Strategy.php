<?php
// app/Models/Strategy.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Strategy extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'description',
        'config',
        'is_public',
    ];

    protected $casts = [
        'config' => 'array',
        'is_public' => 'boolean',
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
