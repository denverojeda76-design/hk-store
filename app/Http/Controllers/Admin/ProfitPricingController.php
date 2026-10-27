<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProfitPricingController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get();

        return view('admin.profit-pricing.index', compact('products'));
    }
}