<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = ['station_id','employee_id','date','total_old_machine','total_new_machine','remaining'];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function deposit_details()
    {
        return $this->hasMany(DepositDetail::class);
    }
}
