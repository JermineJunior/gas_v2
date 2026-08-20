<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelDeliviery extends Model
{
    protected $fillable = ['tuncker_id', 'supplier_id', 'quantity', 'user_id', 'date', 'station_id'];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
