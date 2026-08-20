<?php

namespace App\Http\Controllers;

use App\Models\FuelDeliviery;
use App\Models\Machine;
use App\Models\Station;
use App\Models\Stock;
use App\Models\StockDetail;
use App\Models\Supplier;
use App\Models\Tuncker;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TunckerController extends Controller
{
    public function index(Station $station)
    {
        $stationIds = Auth::user()->stations()->pluck('station_id')->toArray();
        if (!in_array($station->id, $stationIds)) {
            return back();
        }
        $tunckers = Tuncker::where('station_id', $station->id)
            ->orderBy('date', 'desc') // أولاً نرتب بالتاريخ من الأحدث للأقدم
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->date)->format('Y-m'); // تجميع بالسنة والشهر
            })
            ->sortKeysDesc() // نخلي آخر شهر يظهر أول
            ->map(function ($group) {
                // نرتب بيانات كل شهر حسب رقم التنكر تصاعديًا
                return $group->sortBy('tuncker_no');
            });

        return view('list_tuncker', compact('tunckers', 'station'));
    }

    public function create(Station $station)
    {
        $stationIds = Auth::user()->stations()->pluck('station_id')->toArray();
        if (!in_array($station->id, $stationIds)) {
            return back();
        }

        $machines = Machine::where('station_id', $station->id)->get();
        $suppliers = Supplier::get();

        return view('tuncker', compact('station', 'suppliers'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            $tuncker = Tuncker::create([
                'station_id' => $request->station_id,
                'driver_name' => $request->driver_name,
                'tuncker_no' => $request->tuncker_no,
                'date' => $request->date,
                'fuel_type' => $request->fuel_type,
                'fuel_quantity' => $request->fuel_quantity,
                'supplier_id' => $request->supplier_id,
            ]);

            FuelDeliviery::create([
                'tuncker_id' => $tuncker->id,
                'station_id' => $request->station_id,
                'supplier_id' => $request->supplier_id,
                'quantity' => $request->fuel_quantity,
                'date' => $request->date,
                'user_id' => Auth::id(),
            ]);
            $data = [];

            foreach ($request->stock_id as $index => $stock_id) {
                $data[] = [
                    'tuncker_id' => $tuncker->id,
                    'station_id' => $request->station_id,
                    'stock_id' => $stock_id,
                    'qty' => $request->qty[$index],
                ];

                $stock = Stock::find($stock_id);
                $stock->update([
                    'qty' => $stock->qty + $request->qty[$index],
                ]);
            }

            StockDetail::insert($data);
            DB::commit();
            return back()->with('success', 'تم اضافة البيانات بنجاح');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(Tuncker $tuncker)
    {
        $tuncker = $tuncker->load('station', 'stockDetail');
        $suppliers = Supplier::get();
        $stocks = Stock::where('station_id', $tuncker->station_id)->where('type', $tuncker->fuel_type)->get();
        return view('edit_tuncker', compact('tuncker', 'suppliers', 'stocks'));
    }

    public function update(Tuncker $tuncker, Request $request)
    {
        try {
            DB::beginTransaction();
            $old_stock_details = $tuncker->stockDetail;
            foreach ($old_stock_details as $stock_detail) {
                $stock = Stock::find($stock_detail->stock_id);
                $stock->update([
                    'qty' => $stock->qty - $stock_detail->qty,
                ]);
            }

            $tuncker->delete();

            $tuncker = Tuncker::create([
                'station_id' => $request->station_id,
                'driver_name' => $request->driver_name,
                'tuncker_no' => $request->tuncker_no,
                'date' => $request->date,
                'fuel_type' => $request->fuel_type,
                'fuel_quantity' => $request->fuel_quantity,
                'supplier_id' => $request->supplier_id,
            ]);

            FuelDeliviery::create([
                'tuncker_id' => $tuncker->id,
                'station_id' => $request->station_id,
                'supplier_id' => $request->supplier_id,
                'quantity' => $request->fuel_quantity,
                'date' => $request->date,
                'user_id' => Auth::id(),
            ]);
            $data = [];

            foreach ($request->stock_id as $index => $stock_id) {
                $data[] = [
                    'tuncker_id' => $tuncker->id,
                    'station_id' => $request->station_id,
                    'stock_id' => $stock_id,
                    'qty' => $request->qty[$index],
                ];

                $stock = Stock::find($stock_id);
                $stock->update([
                    'qty' => $stock->qty + $request->qty[$index],
                ]);
            }

            StockDetail::insert($data);

            DB::commit();
            return redirect()->route('tuncker.index', $tuncker->station_id)->with('success', 'تم تحديث البيانات بنجاح');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'حدث خطأ ما يرجى المحاولة مرة اخرى');
        }
    }

    public function delete(Tuncker $tuncker)
    {
        foreach($tuncker->stockDetail as $stockDetail){
            $stock = Stock::find($stockDetail->stock_id);
                $stock->update([
                    'qty' => $stock->qty - $stockDetail->qty,
                ]);
        }
        $tuncker->delete();
        return back()->with('success', 'تم حذف البيانات بنجاح');
    }
}
