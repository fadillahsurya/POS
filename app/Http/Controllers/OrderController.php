<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;

class OrderController extends Controller
{
    // List semua transaksi/order
    public function index(Request $request)
    {
        $query = Order::with(['user', 'payment']);

        // Filter status jika ada
        if ($request->filled('status')) {
            $query->where('status_order', $request->status);
        }

        // Search by customer name jika ada
        if ($request->filled('q')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%');
            });
        }

        // Paginate, bawa query string
        $orders = $query->orderByDesc('created_at')->paginate(10)->withQueryString();
        $statuses = ['pending', 'lunas', 'selesai'];

        return view('admin.orders.index', compact('orders', 'statuses'));
    }

    // Form tambah transaksi baru (kasir)
    public function create()
    {
        $products = Product::all();
        $customers = User::whereIn('role', ['customer', 'mitra'])->get();
        return view('admin.orders.create', compact('products', 'customers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'tipe_order'  => 'required|in:offline,online,mitra',
            'produk_id'   => 'required|array',
            'produk_id.*' => 'required|exists:products,id',
            'qty'         => 'required|array',
            'qty.*'       => 'required|numeric|min:1',
        ]);

        // Buat order baru (midtrans_order_id wajib isi, meski offline/manual)
        $order = Order::create([
            'user_id'       => $request->user_id,
            'tipe_order'    => $request->tipe_order,
            'tanggal_order' => now()->toDateString(),
            'total_order'   => 0,
            'status_order'  => 'pending',
            'alamat_kirim'  => $request->alamat_kirim ?? null,
            'catatan'       => $request->catatan ?? null,
            'midtrans_order_id' => 'OFFLINE-' . strtoupper(uniqid()), // <-- WAJIB ADA!
        ]);

        $total = 0;
        foreach ($request->produk_id as $i => $pid) {
            $qty = $request->qty[$i];
            $product = Product::findOrFail($pid);
            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $pid,
                'qty'        => $qty,
                'harga_jual' => $product->harga_jual,
                'subtotal'   => $product->harga_jual * $qty,
            ]);
            $total += $product->harga_jual * $qty;
        }
        $order->update(['total_order' => $total]);
        return redirect()->route('orders.index')->with('success', 'Transaksi berhasil disimpan!');
    }

    // Show detail transaksi/order (pakai midtrans_order_id)
    public function show($midtrans_order_id)
    {
        $order = Order::with(['user', 'items.product', 'payment'])
            ->where('midtrans_order_id', $midtrans_order_id)
            ->firstOrFail();

        return view('admin.orders.show', compact('order'));
    }

    // Proses pembayaran manual/tunai/qris
    public function pay(Request $request, $midtrans_order_id)
    {
        $order = Order::where('midtrans_order_id', $midtrans_order_id)->firstOrFail();
        $previousStatus = $order->status_order;
        $order->status_order = 'lunas';
        $order->save();

        // TIDAK kurangi stok di sini, hanya update status ke lunas
        // Stok akan dikurangi ketika admin ubah status ke "selesai"

        // Bisa tambahkan Payment::create di sini jika perlu catat pembayaran
        // Payment::create([...]);

        return back()->with('success', 'Pembayaran berhasil!');
    }

    // Tandai kirim barang (status jadi selesai & kurangi stok)
    public function kirim($midtrans_order_id)
    {
        $order = Order::with('items.product')
            ->where('midtrans_order_id', $midtrans_order_id)
            ->firstOrFail();

        // Jika belum selesai, baru proses kurangi stok
        if ($order->status_order !== 'selesai') {
            // Kurangi stok tiap produk
            foreach ($order->items as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stok = max(0, $product->stok - $item->qty); // jangan minus
                    $product->save();
                }
            }
            $order->status_order = 'selesai';
            $order->save();
            return back()->with('success', 'Pesanan sudah dikirim & stok barang berkurang!');
        } else {
            // Sudah selesai sebelumnya, stok tidak dikurangi ulang
            return back()->with('info', 'Pesanan sudah selesai. Stok tidak diubah lagi.');
        }
    }

    public function ubahStatus(Request $request, $midtrans_order_id)
    {
        $request->validate([
            'status_order' => 'required|in:pending,lunas,dikirim,selesai',
        ]);

        $order = Order::where('midtrans_order_id', $midtrans_order_id)->firstOrFail();
        $previousStatus = $order->status_order;
        $order->status_order = $request->status_order;
        $order->save();

        // Kurangi stok produk HANYA jika status berubah menjadi "selesai"
        if ($request->status_order === 'selesai' && $previousStatus !== 'selesai') {
            $order->reduceProductStock();
            return back()->with('success', 'Status pesanan berhasil diubah ke Selesai! Stok produk telah dikurangi.');
        }

        return back()->with('success', 'Status pesanan berhasil diubah!');
    }
}