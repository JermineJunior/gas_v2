<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = ['name','phone','user_id'];

    public function fuelOrders()
    {
        return $this->hasMany(FuelOrder::class);
    }

    public function fuelDeliveries()
    {
        return $this->hasMany(FuelDeliviery::class);
    }
}
