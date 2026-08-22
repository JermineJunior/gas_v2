<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DepositDetailController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FuelOrderController;
use App\Http\Controllers\GunContorller;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MachineDetailController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\StationController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TunckerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseWithdrawalController;
use App\Http\Controllers\WarehouseTransactionController;
use App\Http\Controllers\WarehouseTransferController;
use App\Http\Controllers\WarehouseReportController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();
Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [StationController::class, 'index'])->name('station.index');
    Route::post('/', [StationController::class, 'store'])->name('station.store');
    Route::put('station/{station}', [StationController::class, 'update'])->name('station.update');
    Route::delete('station/{station}', [StationController::class, 'destroy'])->name('station.destroy');

    Route::get('users', [UserController::class, 'index'])->name('user.index');
    Route::post('users', [UserController::class, 'store'])->name('user.store');
    Route::put('users', [UserController::class, 'update'])->name('user.update');
    Route::delete('users/{user}', [UserController::class, 'delete'])->name('user.delete');
    Route::post('users/{user}', [UserController::class, 'reset'])->name('user.reset');
    Route::post('/user/update-password', [UserController::class, 'updatePassword'])->name('user.update-password');

    // Roles & Permissions
    Route::resource('roles', RoleController::class)->except(['show']);

    Route::get('report/deposit_detail', [ReportController::class, 'deposit_detail'])->name('reports.deposit_detail');
    Route::post('report/deposit_detail', [ReportController::class, 'deposit_detail_result'])->name('reports.deposit_detail.result');
    Route::get('report/machine_detail', [ReportController::class, 'machine_detail'])->name('reports.machine_detail');
    Route::post('report/machine_detail', [ReportController::class, 'machine_detail_result'])->name('reports.machine_detail.result');
    Route::get('/reports/tuncker', [ReportController::class, 'tuncker'])->name('reports.tuncker');
    Route::post('report/tuncker', [ReportController::class, 'tuncker_result'])->name('reports.tuncker.result');
    Route::get('/reports/supplier', [ReportController::class, 'supplier'])->name('reports.supplier');
    Route::post('report/supplier', [ReportController::class, 'supplier_result'])->name('reports.supplier.result');
    Route::get('/reports/debt', [ReportController::class, 'debt'])->name('reports.debt');
    Route::post('report/debt', [ReportController::class, 'debt_result'])->name('reports.debt.result');

    Route::get('/reports/stock', [ReportController::class, 'machine_report'])->name('reports.machine_report');
    Route::post('report/stock', [ReportController::class, 'machine_report_result'])->name('reports.machine_report.result');
    Route::get('/reports/stock-time', [ReportController::class, 'machine_report_time'])->name('reports.machine_report_time');
    Route::post('report/stock-time', [ReportController::class, 'machine_report_time_result'])->name('reports.machine_report_time.result');

    Route::get('/reports/stock-general', [ReportController::class, 'stock_report'])->name('reports.stock_general');
    Route::post('report/stock-general', [ReportController::class, 'stock_report_result'])->name('reports.stock_general.result');

    Route::get('/reports/stock-movement', [ReportController::class, 'stock_movement'])->name('reports.stock_movement');
    Route::post('report/stock-movement', [ReportController::class, 'stock_movement_result'])->name('reports.stock_movement.result');

    
    Route::post('/machines/store', [MachineController::class, 'storeAjax'])->name('machines.store.ajax');

    Route::get('/api/stocks', function (\Illuminate\Http\Request $request) {
        $query = \App\Models\Stock::query();
        if ($request->station_id) $query->where('station_id', $request->station_id);
        return $query->select('id', 'name', 'type')->get();
    })->name('api.stocks');

    Route::get('/api/machines', function (\Illuminate\Http\Request $request) {
        $query = \App\Models\Machine::query();
        if ($request->station_id) $query->where('station_id', $request->station_id);
        return $query->select('id', 'name')->get();
    })->name('api.machines');

    Route::get('/machine/stock/{machine}', function (\App\Models\Machine $machine) {
        $stock = $machine->stock;
        if (!$stock) {
            return response()->json(['stock' => null]);
        }
        return response()->json([
            'stock' => [
                'id' => $stock->id,
                'name' => $stock->name,
                'qty' => $stock->qty,
                'type' => $stock->type,
                'type_text' => $stock->type == 1 ? 'جازولين' : 'بنزين',
            ],
        ]);
    })->name('api.machine-stock');

    
    Route::get('gen/get_machine',[GunContorller::class,'get_machine'])->name('gun.getMachien');
    Route::get('gen/get_gun',[GunContorller::class,'get_gun'])->name('gun.getGun');
    Route::post('/gun/store', [GunContorller::class, 'storeAjax'])->name('gun.store.ajax');


    Route::get('tuncker/station/{station}', [TunckerController::class, 'index'])->name('tuncker.index');
    Route::get('tuncker/{station}/create', [TunckerController::class, 'create'])->name('tuncker.create');
    Route::post('tuncker', [TunckerController::class, 'store'])->name('tuncker.store');
    Route::get('tuncker/{tuncker}', [TunckerController::class, 'edit'])->name('tuncker.edit');
    Route::put('tuncker/{tuncker}', [TunckerController::class, 'update'])->name('tuncker.update');
    Route::delete('tuncker/{tuncker}', [TunckerController::class, 'delete'])->name('tuncker.delete');

    Route::get('client', [ClientController::class, 'index'])->name('client.index');
    Route::get('client/{station}/station', [ClientController::class, 'station'])->name('client.station');
    Route::post('client', [ClientController::class, 'store'])->name('client.store');
    Route::put('client/{client}', [ClientController::class, 'update'])->name('client.update');
    Route::delete('client/{client}', [ClientController::class, 'delete'])->name('client.delete');
    Route::get('/client-search', [ClientController::class, 'search'])->name('client.search');
    Route::get('/client/{client}', [ClientController::class, 'show'])->name('client.show');

    Route::get('supplier', [SupplierController::class, 'index'])->name('supplier.index');
    Route::post('supplier', [SupplierController::class, 'store'])->name('supplier.store');
    Route::put('supplier', [SupplierController::class, 'update'])->name('supplier.update');
    Route::delete('supplier/{supplier}', [SupplierController::class, 'destroy'])->name('supplier.delete');

    Route::get('revenue/{client}', [RevenueController::class, 'index'])->name('revenue.index');
    Route::post('revenue', [RevenueController::class, 'store'])->name('revenue.store');
    Route::put('revenue/{detail}', [RevenueController::class, 'update'])->name('revenue.update');
    Route::delete('revenue/{detail}', [RevenueController::class, 'delete'])->name('revenue.delete');

    Route::post('/client/details', [ClientController::class, 'store_detail'])->name('client.details.store');
    Route::put('/client/details/{detail}', [ClientController::class, 'update_detail'])->name('client.details.update');
    Route::delete('/client/details/{detail}', [ClientController::class, 'delete_detail'])->name('client.details.delete');

    Route::get('supplier/order/{supplier}', [FuelOrderController::class, 'index'])->name('fuel_order.index');
    Route::post('supplier/order', [FuelOrderController::class, 'store'])->name('fuel_order.store');
    Route::put('supplier/order', [FuelOrderController::class, 'update'])->name('fuel_order.update');
    Route::delete('supplier/order/{fuel_order}', [FuelOrderController::class, 'destroy'])->name('fuel_order.delete');

    Route::get('machine_detail/{station}', [MachineDetailController::class, 'index'])->name('machine_detail.index');
    Route::get('machine_detail/{station}/create', [MachineDetailController::class, 'create'])->name('machine_detail.create');
    Route::post('machine_detail', [MachineDetailController::class, 'store'])->name('machine_detail.store');
    Route::get('machine_detail/{machine_detail}/edit', [MachineDetailController::class, 'edit'])->name('machine_detail.edit');
    Route::put('machine_detail/{machine_detail}', [MachineDetailController::class, 'update'])->name('machine_detail.update');
    Route::delete('machine_detail/{machine_detail}', [MachineDetailController::class, 'destroy'])->name('machine_detail.delete');

    Route::get('deposit_detail/{station}', [DepositDetailController::class, 'index'])->name('deposit_detail.index');
    Route::get('deposit_detail/{station}/create', [DepositDetailController::class, 'create'])->name('deposit_detail.create');
    Route::post('deposit_detail', [DepositDetailController::class, 'store'])->name('deposit_detail.store');
    Route::get('deposit_detail/{deposit}/edit', [DepositDetailController::class, 'edit'])->name('deposit_detail.edit');
    Route::put('deposit_detail/{deposit}', [DepositDetailController::class, 'update'])->name('deposit_detail.update');
    Route::delete('deposit_detail/{deposit_detail}', [DepositDetailController::class, 'destroy'])->name('deposit_detail.delete');

    Route::get('prices', [PriceController::class, 'create'])->name('price.create');
    Route::post('prices', [PriceController::class, 'store'])->name('price.store');

    Route::get('employee/{station}', [EmployeeController::class, 'index'])->name('employee.index');
    Route::post('employee', [EmployeeController::class, 'store'])->name('employee.store');
    Route::put('employee', [EmployeeController::class, 'update'])->name('employee.update');
    Route::delete('employee/{employee}/delete', [EmployeeController::class, 'delete'])->name('employee.delete');
    Route::get('employee-remaining', [EmployeeController::class, 'get_remaining'])->name('employee.get_remaining');

    Route::get('stock/{station}',[StockController::class,'index'])->name('stock.index');
    Route::post('stock', [StockController::class, 'store'])->name('stock.store');
    Route::put('stock', [StockController::class, 'update'])->name('stock.update');
    Route::delete('stock/{stock}/delete', [StockController::class, 'delete'])->name('stock.delete');
    Route::get('/get-stocks-by-type', [StockController::class, 'getByType'])->name('stock.getByType');

    // === Warehouses ===
    Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
    Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
    Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
    Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
    Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');

    // === Warehouse Withdrawals ===
    Route::get('warehouse-withdrawals', [WarehouseWithdrawalController::class, 'index'])->name('warehouse_withdrawals.index');
    Route::get('warehouse-withdrawals/create', [WarehouseWithdrawalController::class, 'create'])->name('warehouse_withdrawals.create');
    Route::post('warehouse-withdrawals', [WarehouseWithdrawalController::class, 'store'])->name('warehouse_withdrawals.store');
    Route::get('warehouse-withdrawals/{warehouse_withdrawal}/edit', [WarehouseWithdrawalController::class, 'edit'])->name('warehouse_withdrawals.edit');
    Route::put('warehouse-withdrawals/{warehouse_withdrawal}', [WarehouseWithdrawalController::class, 'update'])->name('warehouse_withdrawals.update');
    Route::delete('warehouse-withdrawals/{warehouse_withdrawal}', [WarehouseWithdrawalController::class, 'destroy'])->name('warehouse_withdrawals.destroy');

    // === Warehouse Transactions (manual additions) ===
    Route::get('warehouse-transactions', [WarehouseTransactionController::class, 'index'])->name('warehouse_transactions.index');
    Route::get('warehouse-transactions/create', [WarehouseTransactionController::class, 'create'])->name('warehouse_transactions.create');
    Route::post('warehouse-transactions', [WarehouseTransactionController::class, 'store'])->name('warehouse_transactions.store');
    Route::get('warehouse-transactions/{warehouse_transaction}/edit', [WarehouseTransactionController::class, 'edit'])->name('warehouse_transactions.edit');
    Route::put('warehouse-transactions/{warehouse_transaction}', [WarehouseTransactionController::class, 'update'])->name('warehouse_transactions.update');
    Route::delete('warehouse-transactions/{warehouse_transaction}', [WarehouseTransactionController::class, 'destroy'])->name('warehouse_transactions.destroy');

    // === Warehouse Transfers ===
    Route::get('warehouse-transfers', [WarehouseTransferController::class, 'index'])->name('warehouse_transfers.index');
    Route::get('warehouse-transfers/create', [WarehouseTransferController::class, 'create'])->name('warehouse_transfers.create');
    Route::post('warehouse-transfers', [WarehouseTransferController::class, 'store'])->name('warehouse_transfers.store');
    Route::delete('warehouse-transfers/{warehouse_transfer}', [WarehouseTransferController::class, 'destroy'])->name('warehouse_transfers.destroy');

    // === Warehouse Ledger Report ===
    Route::get('reports/warehouse', [WarehouseReportController::class, 'index'])->name('reports.warehouse');
    Route::post('reports/warehouse', [WarehouseReportController::class, 'result'])->name('reports.warehouse.result');

    // === API ===
    Route::get('api/warehouse-stock', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'fuel_type'    => 'required|in:1,2',
        ]);
        $stock = \App\Models\WarehouseStock::where('warehouse_id', $request->warehouse_id)
            ->where('fuel_type', $request->fuel_type)
            ->first();
        return response()->json(['stock' => $stock->current_stock ?? 0]);
    })->name('api.warehouse-stock');
});
