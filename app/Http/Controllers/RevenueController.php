<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RevenueController extends Controller
{
    public function index(Client $client)
    {
        $client->load([
            'details' => function ($query) {
                $query->whereNot('amount', 0)->orderBy('id','DESC');
            },
        ]);

        return view('revenue', compact('client'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'date|required',
            'amount' => 'numeric',
        ]);

        Detail::create([
            'client_id' => $request->client_id,
            'date' => $request->date,
            'amount' => $request->amount,
            'note' => $request->note,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'تم ادخال البيانات بنجاح');
    }

    public function update(Request $request, Detail $detail)
    {
        $request->validate([
            'date' => 'date|required',
            'amount' => 'numeric',
        ]);
        
        $detail->update([
            'date' => $request->date,
            'amount' => $request->amount,
            'note' => $request->note,
        ]);

        return back()->with('success', 'تم تعديل البيانات بنجاح');
    }

    public function delete(Detail $detail)
    {
        $detail->delete();
        return back()->with('success', ' تم حذف البيانات بنجاح');
    }
}
