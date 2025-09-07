<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $produkCount     = Product::count();
        $transaksiCount  = Order::count();
        $stokTotal       = Product::sum('stok');
        $userCount       = User::count();

        return view('admin.dashboard', compact('produkCount', 'transaksiCount', 'stokTotal', 'userCount'));
    }
}