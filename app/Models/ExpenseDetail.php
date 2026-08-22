<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseDetail extends Model
{
    protected $fillable = ['expense_id', 'station_id', 'date', 'expense_amount', 'expense_desc', 'status', 'approved_by', 'approved_at'];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
