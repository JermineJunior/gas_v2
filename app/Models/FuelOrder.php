<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuelOrder extends Model
{
    protected $fillable = ['user_id', 'quantity', 'supplier_id', 'date', 'fuel_type', 'price_per_liter', 'total'];

    protected function casts()
    {
        return [
            'date' => 'date',
            'fuel_type' => 'integer',
            'price_per_liter' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
    
    public function deliveries()
    {
        return $this->hasMany(FuelDeliviery::class);
    }

    public function getDeliveredQuantityAttribute()
    {
        return $this->deliveries->sum('quantity');
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->quantity - $this->delivered_quantity;
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
