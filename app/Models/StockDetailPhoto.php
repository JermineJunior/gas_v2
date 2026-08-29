<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockDetailPhoto extends Model
{
    protected $fillable = [
        'stock_detail_id',
        'path'
    ];

    public function stockDetail()
    {
        return $this->belongsTo(StockDetail::class);
    }
}
