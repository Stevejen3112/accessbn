<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradingBotActivation extends Model
{
    protected $fillable = [
        'user_id',
        'trading_bot_id',
        'amount',
        'leverage',
        'today_roi',
        'returned_profit',
        'today_amount',
        'today_amount_roi',
        'today_cycle_reset_at',
        'next_profit_date',
        'last_profit_date',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'amount' => 'decimal:8',
        'leverage' => 'integer',
        'today_roi' => 'decimal:2',
        'returned_profit' => 'decimal:8',
        'today_amount' => 'decimal:8',
        'today_amount_roi' => 'decimal:2',
        'today_cycle_reset_at' => 'integer',
        'next_profit_date' => 'integer',
        'last_profit_date' => 'integer',
        'start_date' => 'integer',
        'end_date' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bot()
    {
        return $this->belongsTo(TradingBot::class, 'trading_bot_id');
    }

    public function logs()
    {
        return $this->hasMany(TradingBotLog::class);
    }
}
