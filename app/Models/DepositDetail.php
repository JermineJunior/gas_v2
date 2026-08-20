<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositDetail extends Model
{
    protected $fillable = ['deposit_id','deposit_amount','deposit_desc','station_id','date'];

    protected $casts = [
        'date' => 'date'
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function deposit()
    {
        return $this->belongsTo(Deposit::class);
    }
}
