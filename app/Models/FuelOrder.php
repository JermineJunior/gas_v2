<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelOrder extends Model
{
    protected $fillable = ['user_id','quantity','supplier_id','date'];

    protected function casts()
    {
        return [
            'date' => 'date'
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
