<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseTransaction extends Model
{
    // transaction type constants
    const TYPE_ADDITION = 0;
    const TYPE_WITHDRAWAL = 1;
    const TYPE_TRANSFER_OUT = 2;
    const TYPE_TRANSFER_IN = 3;

    protected $fillable = [
        'warehouse_id',
        'fuel_type',
        'type',
        'quantity',
        'balance_after',
        'withdrawal_id',
        'related_transaction_id',
        'driver_name',
        'car_number',
        'date',
        'source',
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

    public function withdrawal()
    {
        return $this->belongsTo(WarehouseWithdrawal::class, 'withdrawal_id');
    }

    public function relatedTransaction()
    {
        return $this->belongsTo(WarehouseTransaction::class, 'related_transaction_id');
    }
}
