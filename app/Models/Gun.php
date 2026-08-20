<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gun extends Model
{
    protected $fillable = ['name','station_id','machine_id'];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
