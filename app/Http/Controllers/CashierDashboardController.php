<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;

class CashierDashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $totalTransaksi = Order::where('tipe_order', 'offline')
            ->whereDate('tanggal_order', $today)->count();

        $omzet = Order::where('tipe_order', 'offline')
            ->whereDate('tanggal_order', $today)->sum('total_order');

        $produkHampirHabis = Product::where('stok', '<=', 10)
            ->orderBy('stok')->limit(5)->get();

        return view('cashier.dashboard', compact('totalTransaksi', 'omzet', 'produkHampirHabis'));
    }
}
