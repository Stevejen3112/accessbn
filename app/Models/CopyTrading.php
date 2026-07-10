<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CopyTrading extends Model
{
    protected $fillable = [
        'code',
        'pair',
        'roi',
        'amount_type',
        'percentage',
        'expires_at',
    ];

    protected $casts = [
        'roi' => 'decimal:2',
        'percentage' => 'float',
        'expires_at' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now()->timestamp);
        });
    }

    public function scopeNotExpired($query)
    {
        return $query;
    }

    public function copyTradingHistories()
    {
        return $this->hasMany(CopyTradingHistory::class);
    }
}
