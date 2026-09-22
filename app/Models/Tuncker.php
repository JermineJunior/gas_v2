<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tuncker extends Model
{
    protected $fillable = ['station_id', 'driver_name', 'tuncker_no', 'date', 'fuel_type', 'fuel_quantity','supplier_id'];

    protected function casts()
    {
        return [
            'date' => 'date',
        ];
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function supplier() : BelongsTo 
    {
        return $this->belongsTo(Supplier::class);    
    }

    public function stockDetail()
    {
        return $this->hasMany(StockDetail::class);
    }

    public function fuelDelivery(): HasOne
    {
        return $this->hasOne(FuelDeliviery::class, 'tuncker_id');
    }
}
