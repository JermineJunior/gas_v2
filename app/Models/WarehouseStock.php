<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WarehouseStock extends Model
{

    protected $fillable = ['warehouse_id', 'fuel_type', 'current_stock'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
