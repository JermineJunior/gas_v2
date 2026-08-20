<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseWithdrawal extends Model
{
    protected $fillable = [
        'warehouse_id',
        'fuel_type',
        'driver_name',
        'car_number',
        'amount',
        'date',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function transaction()
    {
        return $this->hasOne(WarehouseTransaction::class, 'withdrawal_id');
    }
}
