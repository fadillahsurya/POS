<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class HomeController extends Controller
{
    // Tampilkan katalog produk
    public function katalog(Request $request)
{
    $kategori = $request->kategori;
    $categories = Category::orderBy('nama_kategori')->get();

    $products = Product::with('images')
        ->when($kategori, fn($q) => $q->where('category_id', $kategori))
        ->orderBy('created_at', 'desc')
        ->paginate(12);

    return view('home.katalog', compact('products', 'categories', 'kategori'));
}


    // Detail produk
    public function produkDetail($id)
    {
        $produk = Product::with(['images', 'category', 'supplier'])->findOrFail($id);
        return view('home.produk_detail', compact('produk'));
    }

public function beliSekarang(Request $request, $id)
{
    $user = Auth::user();
    $role = $user ? $user->role : 'customer';

    $product = Product::findOrFail($id);
    $qty = (int)($request->input('qty') ?? 1);

    // Validasi role mitra minimal 10 pcs
    if ($role == 'mitra' && $qty < 10) {
        return back()->with('error', 'Minimal pembelian untuk mitra adalah 10 pcs!');
    }

    // Ambil keranjang lama (kalau ada)
    $sessionKey = 'keranjang_' . ($user ? $user->id : 'guest');
    $keranjang = session()->get($sessionKey, []);

    // Tambahkan/Update produk
    if (isset($keranjang[$id])) {
        $keranjang[$id]['qty'] += $qty;
    } else {
        $keranjang[$id] = [
            'id'    => $product->id,
            'nama'  => $product->nama_produk,
            'harga' => $product->harga_jual,
            'qty'   => $qty,
        ];
    }

    session()->put($sessionKey, $keranjang);

    return redirect()->route('home.keranjang')->with('success', 'Produk ditambahkan ke keranjang. Silakan lanjut checkout.');
}


public function keranjang()
{
    $userId = Auth::id() ?: 'guest';
    $sessionKey = 'keranjang_' . $userId;
    $keranjang = collect(session()->get($sessionKey, []));
    $role = Auth::check() ? Auth::user()->role : 'customer';
    return view('home.keranjang', compact('keranjang', 'role'));
}

public function tambahKeranjang(Request $request, $id)
{
    $userId = Auth::id() ?: 'guest'; 
    $sessionKey = 'keranjang_' . $userId;

    $product = Product::findOrFail($id);
    $keranjang = session()->get($sessionKey, []);

    $keranjang[$id] = [
        'id'    => $product->id,
        'nama'  => $product->nama_produk,
        'harga' => $product->harga_jual,
        'qty'   => ($keranjang[$id]['qty'] ?? 0) + 1,
    ];
    session()->put($sessionKey, $keranjang);

    if ($request->ajax() || $request->wantsJson()) {
        $qty = array_sum(array_column($keranjang, 'qty'));
        $total = array_sum(array_map(fn($item) => $item['qty'] * $item['harga'], $keranjang));

        return response()->json([
            'success' => true,
            'qty' => $qty,
            'total' => $total,
        ]);
    }

    return redirect()->route('home.keranjang')->with('success', 'Produk ditambahkan ke keranjang.');
}


public function updateKeranjang(Request $request, $id)
{
    $userId = Auth::id() ?: 'guest';
    $sessionKey = 'keranjang_' . $userId;

    $request->validate([
        'qty' => 'required|integer|min:1|max:999'
    ]);
    $keranjang = session()->get($sessionKey, []);
    if (isset($keranjang[$id])) {
        $keranjang[$id]['qty'] = $request->qty;
        session()->put($sessionKey, $keranjang);
    }
    return back()->with('success', 'Jumlah produk diperbarui.');
}

public function hapusKeranjang($id)
{
    $userId = Auth::id() ?: 'guest';
    $sessionKey = 'keranjang_' . $userId;

    $keranjang = session()->get($sessionKey, []);
    unset($keranjang[$id]);
    session()->put($sessionKey, $keranjang);

    if (request()->ajax() || request()->wantsJson()) {
        return response()->json(['success' => true]);
    }
    return back()->with('success', 'Produk dihapus dari keranjang.');
}

public function checkout(Request $request)
{
    $user = Auth::user();
    $role = $user ? $user->role : 'customer';
    $sessionKey = 'keranjang_' . ($user ? $user->id : 'guest');

    // Jika dari beli sekarang (POST), overwrite keranjang session per user
    if ($request->has('produk_id') && $request->has('qty')) {
        $produk_id = $request->input('produk_id');
        $qty = $request->input('qty');
        $keranjang = [];
        foreach ($produk_id as $i => $pid) {
            $produk = Product::findOrFail($pid);
            $keranjang[$pid] = [
                'id'    => $produk->id,
                'nama'  => $produk->nama_produk,
                'harga' => $produk->harga_jual,
                'qty'   => (int)($qty[$i] ?? 1),
            ];
        }
        session()->put($sessionKey, $keranjang); // Simpan/update session keranjang user
    } else {
        $keranjang = session()->get($sessionKey, []);
    }

    $totalQty = array_sum(array_column($keranjang, 'qty'));

    // === Validasi minimal pembelian mitra ===
    if ($role == 'mitra' && $totalQty < 10) {
        return back()->with('error', 'Minimal pembelian untuk mitra adalah 10 pcs!');
    }
    // ========================================

    // Validasi keranjang tidak kosong
    if (empty($keranjang)) {
        return back()->with('error', 'Keranjang kosong.');
    }

    // Validasi alamat
    $alamat = trim($request->input('alamat', ''));
    if ($alamat !== '' && stripos($alamat, 'tegal') === false) {
        return back()->with('error', 'Jika Anda mengisi alamat, wajib mencantumkan kata "Tegal"!');
    }

    // Hitung total
    $total = 0;
    foreach ($keranjang as $item) {
        $total += $item['qty'] * $item['harga'];
    }

    // Buat midtrans_order_id unik
    $orderPrefix = strtoupper(Str::random(5)) . '-' . time();
    $midtransOrderId = 'INV-' . $orderPrefix;

    // Insert ke orders
    $order = Order::create([
        'user_id' => Auth::id(),
        'tipe_order' => $role == 'mitra' ? 'mitra' : 'customer',
        'tanggal_order' => now(),
        'total_order' => $total,
        'status_order' => 'pending',
        'alamat_kirim' => $alamat,
        'midtrans_order_id' => $midtransOrderId,
    ]);

    // Insert ke order_items
    foreach ($keranjang as $item) {
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $item['id'],
            'qty' => $item['qty'],
            'harga_jual' => $item['harga'],
            'subtotal' => $item['qty'] * $item['harga'],
        ]);
    }

    // Midtrans Payment
    Config::$serverKey = config('midtrans.server_key');
    Config::$isProduction = config('midtrans.is_production');
    Config::$isSanitized = true;
    Config::$is3ds = true;

    try {
        $itemDetails = [];
        foreach ($keranjang as $item) {
            $itemDetails[] = [
                'id' => $item['id'],
                'price' => $item['harga'],
                'quantity' => $item['qty'],
                'name' => $item['nama'],
            ];
        }

        $payload = [
            'transaction_details' => [
                'order_id' => $midtransOrderId,
                'gross_amount' => $total,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
            ],
            'enabled_payments' => ['bank_transfer', 'gopay', 'qris'],
        ];

        $snapToken = Snap::getSnapToken($payload);

        Payment::create([
            'order_id' => $order->id,
            'midtrans_order_id' => $midtransOrderId,
            'jumlah_bayar' => $total,
            'metode_bayar' => 'midtrans',
            'status_bayar' => 'pending',
        ]);

        // Kosongkan keranjang setelah order dibuat
        session()->forget($sessionKey);

        return view('home.snap_checkout', [
            'snapToken' => $snapToken,
            'order' => $order,
        ]);
    } catch (\Exception $e) {
        Log::error('[Checkout Error] ' . $e->getMessage());
        return redirect()->route('home.keranjang')->with('error', 'Gagal membuat Snap Token: ' . $e->getMessage());
    }
}

    public function checkoutSuccess()
    {
        return view('home.checkout_success');
    }

    // Tampilkan daftar pesanan user
    public function pesananSaya()
    {
        $userId = Auth::id();
        // Auto update status 'pending' yang sudah >1 jam jadi 'gagal'
        Order::where('user_id', $userId)
            ->where('status_order', 'pending')
            ->where('created_at', '<', Carbon::now()->subHour())
            ->update(['status_order' => 'gagal']);

        $orders = Order::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->get();

        return view('home.orders', compact('orders'));
    }

    // Cancel order
    public function cancelOrder($id)
    {
        $order = Order::where('user_id', Auth::id())
            ->where('status_order', 'pending')
            ->findOrFail($id);

        $order->status_order = 'gagal';
        $order->save();

        return redirect()->route('home.myorders.index')->with('success', 'Pesanan berhasil dibatalkan.');
    }

    // Detail pesanan
    public function pesananDetail($id)
    {
        $order = Order::with('items.product')
            ->where('user_id', Auth::id())
            ->findOrFail($id);
        return view('home.order_detail', compact('order'));
    }

    // Lanjutkan pembayaran order
    public function lanjutkanPembayaran($id)
    {
        $order = Order::with('items.product')
            ->where('user_id', Auth::id())
            ->where('id', $id)
            ->where('status_order', 'pending')
            ->firstOrFail();

        $user = Auth::user();

        // Setup Midtrans config
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = true;
        Config::$is3ds = true;

        try {
            // Cek status Midtrans
            try {
                $midtransStatus = Transaction::status($order->midtrans_order_id);
                $transactionStatus = $midtransStatus->transaction_status ?? 'unknown';

                // Sudah sukses
                if (in_array($transactionStatus, ['settlement', 'capture', 'success'])) {
                    return redirect()->route('home.myorders.detail', $id)
                        ->with('info', 'Pembayaran sudah berhasil diproses.');
                }
                // Sudah expired/cancel/deny/failure
                if (in_array($transactionStatus, ['expire', 'cancel', 'deny', 'failure'])) {
                    return redirect()->route('home.myorders.detail', $id)
                        ->with('error', 'Transaksi sudah tidak valid. Status: ' . $transactionStatus);
                }
                // Pending: lanjutkan
            } catch (\Exception $statusError) {
                // Biarkan lanjut
            }

            // Generate Snap Token baru
            $itemDetails = [];
            foreach ($order->items as $orderItem) {
                $itemDetails[] = [
                    'id' => $orderItem->product_id,
                    'price' => $orderItem->harga_jual,
                    'quantity' => $orderItem->qty,
                    'name' => $orderItem->product->nama_produk ?? 'Product #' . $orderItem->product_id,
                ];
            }

            $payload = [
                'transaction_details' => [
                    'order_id' => $order->midtrans_order_id,
                    'gross_amount' => $order->total_order,
                ],
                'item_details' => $itemDetails,
                'customer_details' => [
                    'first_name' => $user->name,
                    'email' => $user->email,
                ],
            ];

            $snapToken = Snap::getSnapToken($payload);
            session(['snap_token_' . $order->id => $snapToken]);

            return view('home.snap_checkout', [
                'snapToken' => $snapToken,
                'order' => $order,
                'isRetry' => true
            ]);
        } catch (\Exception $e) {
            // Jika error "order_id has already been taken", coba order id baru
            if (strpos($e->getMessage(), 'has already been taken') !== false) {
                $newOrderId = $order->midtrans_order_id . '-R' . time();
                $itemDetails = [];
                foreach ($order->items as $orderItem) {
                    $itemDetails[] = [
                        'id' => $orderItem->product_id,
                        'price' => $orderItem->harga_jual,
                        'quantity' => $orderItem->qty,
                        'name' => $orderItem->product->nama_produk ?? 'Product #' . $orderItem->product_id,
                    ];
                }
                $newPayload = [
                    'transaction_details' => [
                        'order_id' => $newOrderId,
                        'gross_amount' => $order->total_order,
                    ],
                    'item_details' => $itemDetails,
                    'customer_details' => [
                        'first_name' => $user->name,
                        'email' => $user->email,
                    ],
                ];
                $snapToken = Snap::getSnapToken($newPayload);
                $order->midtrans_order_id = $newOrderId;
                $order->save();
                session(['snap_token_' . $order->id => $snapToken]);
                return view('home.snap_checkout', [
                    'snapToken' => $snapToken,
                    'order' => $order,
                    'isRetry' => true
                ]);
            }

            Log::error('[Lanjutkan Pembayaran Error] ' . $e->getMessage());
            return redirect()->route('home.myorders.detail', $id)
                ->with('error', 'Gagal melanjutkan pembayaran: ' . $e->getMessage());
        }
    }
}
