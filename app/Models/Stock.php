<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = ['name','station_id','qty','type'];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function machines()
    {
        return $this->hasMany(Machine::class);
    }
}
