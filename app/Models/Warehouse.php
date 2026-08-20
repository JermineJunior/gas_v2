<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{

    protected $fillable = ['name'];
    public function stocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    public function transactions()
    {
        return $this->hasMany(WarehouseTransaction::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(WarehouseWithdrawal::class);
    }

    /**
     * Recalculates balance_after for every transaction belonging to this
     * warehouse + fuel type combination, in chronological order, and updates
     * (or creates) the corresponding WarehouseStock's current_stock.
     *
     * Call this any time a transaction for this warehouse+fuel_type is
     * created, updated, or deleted — guarantees correctness even for
     * out-of-order edits.
     */
    public static function recalculateBalance(int $warehouseId, int $fuelType): void
    {
        $transactions = WarehouseTransaction::where('warehouse_id', $warehouseId)
            ->where('fuel_type', $fuelType)
            ->orderBy('date')
            ->orderBy('id') // tie-break for same-day transactions
            ->get();

        $balance = 0;

        foreach ($transactions as $transaction) {
            // type 0 (addition) and 3 (transfer in) increase stock
            // type 1 (withdrawal) and 2 (transfer out) decrease stock
            $balance += in_array($transaction->type, [0, 3])
                ? $transaction->quantity // في حالة الزيادة
                : -$transaction->quantity; // في حالة النقصان

            $transaction->update(['balance_after' => $balance]);
        }

        WarehouseStock::updateOrCreate(
            ['warehouse_id' => $warehouseId, 'fuel_type' => $fuelType],
            ['current_stock' => $balance]
        );
    }
}
