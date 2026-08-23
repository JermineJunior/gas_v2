<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Machine extends Model
{
    protected $fillable = ['name', 'station_id', 'stock_id', 'max_counter', 'use_rollover'];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function guns(): HasMany
    {
        return $this->hasMany(Gun::class);
    }
}
