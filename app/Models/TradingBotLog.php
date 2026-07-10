<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradingBotLog extends Model
{
    protected $fillable = [
        'user_id',
        'trading_bot_activation_id',
        'trading_pair',
        'exchange',
        'type',
        'amount',
        'profit',
        'profit_percentage',
        'exit_time',
        'leverage',
        'exit_price',
        'direction',
        'message',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'profit' => 'decimal:8',
        'profit_percentage' => 'decimal:2',
        'exit_time' => 'integer',
        'leverage' => 'integer',
        'exit_price' => 'decimal:8',
    ];

    public function activation()
    {
        return $this->belongsTo(TradingBotActivation::class, 'trading_bot_activation_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
