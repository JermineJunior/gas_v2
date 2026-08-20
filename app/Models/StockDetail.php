<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockDetail extends Model
{
    protected $fillable = ['station_id','stock_id','tuncker_id','qty'];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }
}
