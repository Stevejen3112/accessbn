<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CopyTradingHistory extends Model
{
    protected $fillable = [
        'user_id',
        'copy_trading_id',
        'amount',
        'pair',
        'copy_code',
        'roi',
        'profit',
        'status',
        'activated_at',
        'completes_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        $decimal_places = getSetting('decimal_places');
        return [
            'amount' => 'decimal:' . $decimal_places,
            'profit' => 'decimal:' . $decimal_places,
            'roi' => 'decimal:2',
            'activated_at' => 'datetime',
            'completes_at' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function copyTrading()
    {
        return $this->belongsTo(CopyTrading::class, 'copy_trading_id');
    }
}
