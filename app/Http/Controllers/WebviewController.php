<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WebviewController extends Controller
{

    /*
     * Cart view for show the current orders
     */
    public function cart(Request $request ){

        return view('orders.cart');
    }
}
