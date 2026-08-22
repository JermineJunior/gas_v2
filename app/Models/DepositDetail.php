<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositDetail extends Model
{
    protected $fillable = ['deposit_id','station_id','date','deposit_amount','deposit_desc','status','approved_by','approved_at'];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function deposit()
    {
        return $this->belongsTo(Deposit::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}

