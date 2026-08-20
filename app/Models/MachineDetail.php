<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineDetail extends Model
{
    protected $fillable = ['station_id','gun_id', 'machine_id', 'start_counter', 'end_counter', 'net', 'price', 'total','status','employee_id'];

    protected $casts = [
        'date' => 'date',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function gun()
    {
        return $this->belongsTo(Gun::class);
    }
}
