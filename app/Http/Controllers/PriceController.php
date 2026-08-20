<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriceController extends Controller
{
    public function create()
    {
        return view('price');
    }

    public function store(Request $request)
    {
        Auth::user()->update($request->only('price_customer','price_bus'));

        return redirect()->route('price.create')->with('success',' تم تغيير الاسعار بنجاح');
    }
}
