<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradingBot extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'logo',
        'type',
        'exchanges',
        'traded_pairs',
        'min_amount',
        'max_amount',
        'is_active',
        'daily_return_min',
        'daily_return_max',
        'duration',
        'duration_type',
        'trading_days',
        'is_capital_returned',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'exchanges' => 'array',
        'traded_pairs' => 'array',
        'trading_days' => 'array',
        'is_active' => 'boolean',
        'is_capital_returned' => 'boolean',
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'daily_return_min' => 'decimal:2',
        'daily_return_max' => 'decimal:2',
        'duration' => 'integer',
    ];

    /**
     * Scope a query to only include active bots.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
