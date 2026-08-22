<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['station_id', 'user_id', 'date'];

    protected $casts = [
        'date' => 'date',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function expense_details()
    {
        return $this->hasMany(ExpenseDetail::class);
    }

    /**
     * حالة المصروف الكلية محسوبة مباشرة من البنود (بدون عمود في قاعدة البيانات)
     */
    public function getStatusAttribute(): string
    {
        $details = $this->expense_details;

        if ($details->isEmpty() || $details->every(fn($d) => $d->status == 0)) {
            return 'pending';
        }

        if ($details->every(fn($d) => $d->status == 1)) {
            return 'approved';
        }

        return 'partially_approved';
    }
}
