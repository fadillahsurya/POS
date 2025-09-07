<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function penjualan(Request $request)
    {
        $tglAwal = $request->get('tgl_awal', now()->format('Y-m-01'));
        $tglAkhir = $request->get('tgl_akhir', now()->format('Y-m-d'));

        $orders = Order::whereBetween('tanggal_order', [$tglAwal, $tglAkhir])
            ->where('status_order', 'selesai')
            ->with('user')
            ->orderBy('tanggal_order', 'desc')
            ->get();

        $total = $orders->sum('total_order');

        return view('admin.laporan.penjualan', compact('orders', 'tglAwal', 'tglAkhir', 'total'));
    }

    public function produkTerlaris(Request $request)
    {
        $tglAwal = $request->get('tgl_awal', now()->format('Y-m-01'));
        $tglAkhir = $request->get('tgl_akhir', now()->format('Y-m-d'));

        $terlaris = OrderItem::select('product_id', DB::raw('SUM(qty) as total_terjual'))
            ->whereHas('order', function ($q) use ($tglAwal, $tglAkhir) {
                $q->where('status_order', 'selesai')
                    ->whereBetween('tanggal_order', [$tglAwal, $tglAkhir]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_terjual')
            ->with('product')
            ->take(10)
            ->get();

        return view('admin.laporan.produk_terlaris', compact('terlaris', 'tglAwal', 'tglAkhir'));
    }

    public function pembelian(Request $request)
    {
        $tglAwal = $request->get('tgl_awal', now()->format('Y-m-01'));
        $tglAkhir = $request->get('tgl_akhir', now()->format('Y-m-d'));

        $purchases = Purchase::whereBetween('created_at', [$tglAwal, $tglAkhir])
            ->with('supplier')
            ->orderBy('created_at', 'desc')
            ->get();

        $total = $purchases->sum('total_beli'); // Perbaiki dari 'total_harga' ke 'total_beli'

        return view('admin.laporan.pembelian', compact('purchases', 'tglAwal', 'tglAkhir', 'total'));
    }

    // Cetak PDF Penjualan
    public function penjualanPdf(Request $request)
    {
        $tglAwal = $request->get('tgl_awal', now()->format('Y-m-01'));
        $tglAkhir = $request->get('tgl_akhir', now()->format('Y-m-d'));
        $orders = Order::whereBetween('tanggal_order', [$tglAwal, $tglAkhir])
            ->where('status_order', 'selesai')
            ->with('user')
            ->orderBy('tanggal_order', 'desc')
            ->get();

        $total = $orders->sum('total_order');

        $pdf = Pdf::loadView('admin.laporan.penjualan_pdf', compact('orders', 'tglAwal', 'tglAkhir', 'total'));
        return $pdf->download('laporan_penjualan_' . $tglAwal . '_sd_' . $tglAkhir . '.pdf');
    }

    // Cetak PDF Pembelian
    public function pembelianPdf(Request $request)
    {
        $tglAwal = $request->get('tgl_awal', now()->format('Y-m-01'));
        $tglAkhir = $request->get('tgl_akhir', now()->format('Y-m-d'));
        $purchases = Purchase::whereBetween('tanggal_beli', [$tglAwal, $tglAkhir])
            ->with(['supplier', 'purchaseItems.product'])
            ->orderBy('tanggal_beli', 'desc')
            ->get();

        $total = $purchases->sum('total_beli');

        $pdf = Pdf::loadView('admin.laporan.pembelian_pdf', compact('purchases', 'tglAwal', 'tglAkhir', 'total'));
        return $pdf->download('laporan_pembelian_' . $tglAwal . '_sd_' . $tglAkhir . '.pdf');
    }

    // Cetak PDF Produk Terlaris
    public function produkTerlarisPdf(Request $request)
    {
        $tglAwal = $request->get('tgl_awal', now()->format('Y-m-01'));
        $tglAkhir = $request->get('tgl_akhir', now()->format('Y-m-d'));
        
        $produkTerlaris = OrderItem::select('product_id', DB::raw('SUM(qty) as total_terjual'))
            ->whereHas('order', function ($q) use ($tglAwal, $tglAkhir) {
                $q->where('status_order', 'selesai')
                  ->whereBetween('tanggal_order', [$tglAwal, $tglAkhir]);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_terjual')
            ->with('product.category') // Eager load product dan category
            ->take(10) // Limit sama dengan view biasa
            ->get();

        $totalKeseluruhan = $produkTerlaris->sum('total_terjual');

        $pdf = Pdf::loadView('admin.laporan.produk_terlaris_pdf', compact('produkTerlaris', 'tglAwal', 'tglAkhir', 'totalKeseluruhan'));
        return $pdf->download('laporan_produk_terlaris_' . $tglAwal . '_sd_' . $tglAkhir . '.pdf');
    }
    //     // Detail transaksi per order
    // public function penjualanDetail($id)
    // {
    //     $order = Order::with(['user', 'orderItems.product'])->findOrFail($id);

    //     return view('admin.laporan.penjualan_detail', compact('order'));
    // }

    // // Cetak detail transaksi (per order) ke PDF
    // public function penjualanDetailPdf($id)
    // {
    //     $order = Order::with(['user', 'orderItems.product'])->findOrFail($id);

    //     $pdf = Pdf::loadView('admin.laporan.penjualan_detail_pdf', compact('order'))
    //               ->setPaper('A4', 'portrait');

    //     return $pdf->download('transaksi_'.$order->id.'.pdf');
    // }

}
