<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Machine extends Model
{
    protected $fillable = ['name','station_id','stock_id'];

    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }
}
