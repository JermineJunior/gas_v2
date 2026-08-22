<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Detail;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::whereUser_id(Auth::id())
            ->withSum('details as total_sum', 'total')
            ->withSum('details as paid_sum', 'amount')
            ->addSelect(['last_payment_date' => Detail::selectRaw('MAX(date)')
                ->whereColumn('client_id', 'clients.id')
                ->where('amount', '>', 0)])
            ->get();
        return view('client', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:clients,name,NULL,id,user_id,' . Auth::id(),
            'type' => 'required|in:1,2',
            'phone' => 'nullable|string|max:255',
        ]);

        Client::create([
            'name' => $request->name,
            'type' => $request->type,
            'phone' => $request->phone,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'تم اضافة العميل بنجاح');
    }

    public function update(Client $client, Request $request)
    {
        if (Auth::id() != $client->user_id) {
            return back();
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:clients,name,' . $client->id . ',id,user_id,' . Auth::id(),
            'type' => 'required|in:1,2',
            'phone' => 'nullable|string|max:255',
        ]);

        $client->update([
            'name' => $request->name,
            'type' => $request->type,
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'تم تحديث بيانات العميل  بنجاح');
    }

    public function delete(Client $client)
    {
        if (Auth::id() != $client->user_id) {
            return back();
        }
        $client->delete();
        return back()->with('success', 'تم حذف بيانات العميل بنجاح');
    }

    public function station(Station $station)
    {
        $user = $station->users()->where('type', 3)->first();
        if (!$user) {
            $user = $station->users()->where('type', 2)->first();
        }
        $clients = client::where('user_id', $user->id)
            ->withSum('details as total_sum', 'total')
            ->withSum('details as paid_sum', 'amount')
            ->addSelect(['last_payment_date' => Detail::selectRaw('MAX(date)')
                ->whereColumn('client_id', 'clients.id')
                ->where('amount', '>', 0)])
            ->get();
        return view('client', compact('clients', 'station'));
    }

    public function search(Request $request)
    {
        $keyword = $request->keyword;
        $clients = Client::where('user_id', Auth::id())
            ->withSum('details as total_sum', 'total')
            ->withSum('details as paid_sum', 'amount')
            ->addSelect(['last_payment_date' => Detail::selectRaw('MAX(date)')
                ->whereColumn('client_id', 'clients.id')
                ->where('amount', '>', 0)])
            ->where('name', 'LIKE', "%$keyword%")
            ->orderBy('id', 'DESC')
            ->get();
        return response()->json($clients);
    }

    public function show(Client $client)
    {
        $client = $client->load([
            'details' => function ($query) {
                $query->whereNot('total', 0)->orderBy('id', 'DESC');
            },
        ]);
        $lastPaymentDate = $client->details()->where('amount', '>', 0)->max('date');
        return view('detail', compact('client', 'lastPaymentDate'));
    }

    public function store_detail(Request $request)
    {
        $request->validate([
            'date' => 'date|required',
            'liter' => 'numeric',
            'price' => 'numeric',
            'total' => 'numeric',
        ]);

        Detail::create([
            'date' => $request->date,
            'client_id' => $request->client_id,
            'liter' => $request->liter,
            'price' => $request->price,
            'total' => $request->total,
            'note' => $request->note,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', ' تم ادخال البيانات بنجاح');
    }

    public function update_detail(Detail $detail, Request $request)
    {
        $request->validate([
            'date' => 'date|required',
            'liter' => 'numeric',
            'price' => 'numeric',
            'total' => 'numeric',
        ]);

        $detail->update([
            'date' => $request->date,
            'liter' => $request->liter,
            'price' => $request->price,
            'total' => $request->total,
            'note' => $request->note,
        ]);

        return back()->with('success', ' تم تعديل البيانات بنجاح');
    }

    public function delete_detail(Detail $detail)
    {
        $detail->delete();
        return back()->with('success', 'تم حذف البيانات بنجاح');
    }

}
