<?php

namespace App\Http\Controllers;

use App\Models\FuelDeliviery;
use App\Models\Machine;
use App\Models\Station;
use App\Models\Stock;
use App\Models\StockDetail;
use App\Models\StockDetailPhoto;
use App\Models\Supplier;
use App\Models\Tuncker;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            $fuelTotals = Tuncker::where('station_id', $station->id)
                ->selectRaw('fuel_type, SUM(fuel_quantity) as total')
                ->groupBy('fuel_type')
                ->pluck('total', 'fuel_type');
            $total_amount = $fuelTotals->sum();
            $total_gasoline = $fuelTotals[1] ?? 0;
            $total_benzine = $fuelTotals[2] ?? 0;
        return view('list_tuncker', compact('tunckers', 'station', 'total_amount', 'total_gasoline', 'total_benzine'));
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
        // صور العدادات اختيارية — لو وُجدت تُفحص كصور (بحد أقصى 5 ميغا لكل صورة)
        $request->validate([
            'meter_photos.*.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

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

            //عند التفريغ في بير واحد اواكثر
            foreach ($request->stock_id as $index => $stock_id) {
                $stockDetail = StockDetail::create([
                    'tuncker_id' => $tuncker->id,
                    'station_id' => $request->station_id,
                    'stock_id' => $stock_id,
                    'qty' => $request->qty[$index],
                ]);

                $stock = Stock::find($stock_id);
                $stock->update([
                    'qty' => $stock->qty + $request->qty[$index],
                ]);

                // صور اختيارية مرفوعة لهذا الصف — المفتاح $index يطابق qty[$index]/stock_id[$index]
                if ($request->hasFile("meter_photos.$index")) {
                    foreach ($request->file("meter_photos.$index") as $file) {
                        $path = Storage::disk('public')->putFile('meter_photos', $file);
                        StockDetailPhoto::create([
                            'stock_detail_id' => $stockDetail->id,
                            'path' => $path,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect('/')->with('success', 'تم اضافة البيانات بنجاح');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(Tuncker $tuncker)
    {
        $tuncker = $tuncker->load('station', 'stockDetail.photos');
        $suppliers = Supplier::get();
        $stocks = Stock::where('station_id', $tuncker->station_id)->where('type', $tuncker->fuel_type)->get();
        return view('edit_tuncker', compact('tuncker', 'suppliers', 'stocks'));
    }

    public function update(Tuncker $tuncker, Request $request)
    {
        // صور العدادات اختيارية — لو وُجدت تُفحص كصور (بحد أقصى 5 ميغا لكل صورة)
        $request->validate([
            'meter_photos.*.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        try {
            DB::beginTransaction();
            $old_stock_details = $tuncker->stockDetail->load('photos');
            foreach ($old_stock_details as $stock_detail) {
                $stock = Stock::find($stock_detail->stock_id);
                $stock->update([
                    'qty' => $stock->qty - $stock_detail->qty,
                ]);
                // حذف ملفات الصور القديمة مع صفوفها (صفوف الصور تُحذف تلقائياً بالـ cascade)
                foreach ($stock_detail->photos as $photo) {
                    Storage::disk('public')->delete($photo->path);
                }
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

            foreach ($request->stock_id as $index => $stock_id) {
                $stockDetail = StockDetail::create([
                    'tuncker_id' => $tuncker->id,
                    'station_id' => $request->station_id,
                    'stock_id' => $stock_id,
                    'qty' => $request->qty[$index],
                ]);

                $stock = Stock::find($stock_id);
                $stock->update([
                    'qty' => $stock->qty + $request->qty[$index],
                ]);

                // صور اختيارية مرفوعة لهذا الصف — المفتاح $index يطابق qty[$index]/stock_id[$index]
                if ($request->hasFile("meter_photos.$index")) {
                    foreach ($request->file("meter_photos.$index") as $file) {
                        $path = Storage::disk('public')->putFile('meter_photos', $file);
                        StockDetailPhoto::create([
                            'stock_detail_id' => $stockDetail->id,
                            'path' => $path,
                        ]);
                    }
                }
            }

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
            foreach ($stockDetail->photos as $photo) {
                Storage::disk('public')->delete($photo->path);
            }
        }
        $tuncker->delete();
        return back()->with('success', 'تم حذف البيانات بنجاح');
    }
}
