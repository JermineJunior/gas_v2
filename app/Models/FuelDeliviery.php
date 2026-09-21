<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelDeliviery extends Model
{
    protected $fillable = ['tuncker_id', 'fuel_order_id', 'supplier_id', 'quantity', 'user_id', 'date', 'station_id'];

    protected function casts()
    {
        return [
            'date' => 'date'
        ];
    }
    public function fuelOrder()
    {
        return $this->belongsTo(FuelOrder::class);
    }
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

    public function tuncker()
    {
        return $this->belongsTo(Tuncker::class);
    }
}
