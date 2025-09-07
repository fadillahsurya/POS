<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Models\Product;
use App\Models\ProductFlavor; // <— TAMBAH: model varian
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

class PosController extends Controller
{
    /* -----------------------------
     | Helpers (session cart)
     | ----------------------------- */
    private function cart(): array
    {
        return session('pos_cart', []);
    }

    private function putCart(array $cart): void
    {
        session(['pos_cart' => $cart]);
    }

    /* -----------------------------
     | Halaman POS
     | ----------------------------- */
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q'));

        // cari produk berdasarkan nama/sku
        $products = Product::query()
            ->with('flavors') // <— eager load varian
            ->when($q, function ($s) use ($q) {
                $s->where('nama_produk', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%");
            })
            ->orderBy('nama_produk')
            ->limit(50)
            ->get();

        // pelanggan yang bisa dipilih (opsional)
        $customers = User::whereIn('role', ['customer', 'mitra'])
            ->where('is_active', 1)
            ->orderBy('name')
            ->get();

        $cart = $this->cart();
        $subtotal = collect($cart)->sum(fn($r) => $r['qty'] * $r['harga_jual']);

        return view('cashier.pos', compact('products', 'customers', 'cart', 'subtotal'));
    }

    /* -----------------------------
     | Tambah item ke keranjang (support varian)
     | ----------------------------- */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty'        => 'required|integer|min:1',
            'flavor_id'  => 'nullable|integer',
        ]);

        $p = Product::findOrFail($request->product_id);

        // Validasi varian (jika dikirim)
        $flavor = null;
        if ($request->filled('flavor_id')) {
            $flavor = ProductFlavor::where('product_id', $p->id)->find($request->flavor_id);
            if (!$flavor) {
                return back()->with('error', 'Varian rasa tidak valid.');
            }
        }

        $cart = $this->cart();

        // Key unik per produk:varian (varian 0 = tanpa varian)
        $key = $p->id . ':' . ($flavor?->id ?? 0);

        $inCart     = isset($cart[$key]) ? (int) $cart[$key]['qty'] : 0;
        $requestQty = (int) $request->qty;

        $available = (int) ($flavor ? $flavor->stok : $p->stok);
        if ($inCart + $requestQty > $available) {
            $label = $flavor ? "{$p->nama_produk} - {$flavor->nama_rasa}" : $p->nama_produk;
            return back()->with('error', "Stok {$label} tidak mencukupi.");
        }

        $unitPrice = (float) $p->harga_jual + (float) ($flavor->harga_tambahan ?? 0);

        $cart[$key] = [
            'key'         => $key,
            'product_id'  => $p->id,
            'flavor_id'   => $flavor?->id,
            'nama'        => $p->nama_produk . ($flavor ? " - {$flavor->nama_rasa}" : ''),
            'variant_label' => $flavor?->nama_rasa,
            'harga_jual'  => $unitPrice,
            'qty'         => $inCart + $requestQty,
            'stok'        => $available,
            'sku'         => $p->sku ?? null,
        ];

        $this->putCart($cart);
        return back()->with('success', 'Item ditambahkan ke keranjang.');
    }

    /* -----------------------------
     | Update jumlah item (pakai key)
     | ----------------------------- */
    public function update(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'qty' => 'required|integer|min:0',
        ]);

        $cart = $this->cart();

        if (!isset($cart[$request->key])) {
            return back()->with('error', 'Item tidak ada di keranjang.');
        }

        $row   = $cart[$request->key];
        $newQty = (int) $request->qty;

        if ($newQty === 0) {
            unset($cart[$request->key]);
            $this->putCart($cart);
            return back()->with('success', 'Item dihapus dari keranjang.');
        }

        // Recheck stok live
        $p = Product::find($row['product_id']);
        if (!$p) {
            return back()->with('error', 'Produk tidak ditemukan.');
        }

        $available = 0;
        if (!empty($row['flavor_id'])) {
            $fv = ProductFlavor::where('product_id', $p->id)->find($row['flavor_id']);
            if (!$fv) return back()->with('error', 'Varian tidak ditemukan.');
            $available = (int) $fv->stok;
        } else {
            $available = (int) $p->stok;
        }

        if ($newQty > $available) {
            return back()->with('error', 'Jumlah melebihi stok tersedia.');
        }

        $cart[$request->key]['qty'] = $newQty;
        $this->putCart($cart);

        return back()->with('success', 'Jumlah item diperbarui.');
    }

    /* -----------------------------
     | Hapus item (pakai key)
     | ----------------------------- */
    public function remove(Request $request)
    {
        // dukung kompat lama: key atau product_id
        $request->validate([
            'key' => 'nullable|string',
            'product_id' => 'nullable|integer',
        ]);

        $cart = $this->cart();

        if ($request->filled('key')) {
            unset($cart[$request->key]);
        } elseif ($request->filled('product_id')) {
            // hapus semua item dengan product_id tsb (varian apapun)
            foreach ($cart as $k => $row) {
                if ((int)$row['product_id'] === (int)$request->product_id) {
                    unset($cart[$k]);
                }
            }
        }

        $this->putCart($cart);
        return back()->with('success', 'Item dihapus.');
    }

    /* -----------------------------
     | Kosongkan keranjang
     | ----------------------------- */
    public function clear()
    {
        session()->forget('pos_cart');
        return back()->with('success', 'Keranjang dikosongkan.');
    }

    /* -----------------------------
     | Checkout (kurangi stok per-varian bila ada)
     | ----------------------------- */
    public function checkout(Request $request)
    {
        $request->validate([
            'paid'        => 'required|numeric|min:0',
            'discount'    => 'nullable|numeric|min:0',
            'customer_id' => 'nullable|exists:users,id',
            'catatan'     => 'nullable|string|max:255',
        ]);

        $cart = $this->cart();
        if (empty($cart)) {
            return back()->with('error', 'Keranjang masih kosong.');
        }

        // hitung total
        $subtotal = collect($cart)->sum(fn($r) => $r['qty'] * $r['harga_jual']);
        $discount = (float) ($request->discount ?? 0);
        $grand    = max(0, $subtotal - $discount);

        if ((float) $request->paid < $grand) {
            return back()->with('error', 'Uang dibayar kurang dari total.');
        }

        DB::transaction(function () use ($request, $cart, $subtotal, $discount) {

            // tentukan customer: pilih dari form atau fallback "Pelanggan Umum"
            $customerId = $request->customer_id;
            if (!$customerId) {
                $walkin = User::firstOrCreate(
                    ['email' => 'walkin@kasir.local'],
                    [
                        'name'      => 'Pelanggan Umum',
                        'password'  => Hash::make(Str::random(32)),
                        'role'      => 'customer',
                        'is_active' => 1,
                    ]
                );
                $customerId = $walkin->id;
            }

            // buat order POS (offline)
            $order = Order::create([
                'user_id'          => $customerId,
                'tipe_order'       => 'offline',
                'tanggal_order'    => Carbon::today()->toDateString(),
                'total_order'      => 0,                // diisi setelah item
                'status_order'     => 'selesai',        // POS langsung selesai
                'alamat_kirim'     => null,
                'catatan'          => $request->catatan,
                'midtrans_order_id' => 'OFFLINE-' . strtoupper(uniqid()),
            ]);

            $total = 0;

            foreach ($cart as $row) {
                // lock produk
                $p = Product::lockForUpdate()->find($row['product_id']);
                if (!$p) continue;

                $qty = (int) $row['qty'];
                $unitPrice = (float) $row['harga_jual'];

                if (!empty($row['flavor_id'])) {
                    // lock varian
                    $fv = ProductFlavor::lockForUpdate()
                        ->where('product_id', $p->id)
                        ->find($row['flavor_id']);

                    if (!$fv) abort(422, "Varian tidak ditemukan.");
                    if ($qty > (int)$fv->stok) {
                        abort(422, "Stok {$p->nama_produk} - {$fv->nama_rasa} tidak mencukupi.");
                    }
                } else {
                    if ($qty > (int)$p->stok) {
                        abort(422, "Stok {$p->nama_produk} tidak mencukupi.");
                    }
                }

                // simpan item order
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $p->id,
                    'qty'        => $qty,
                    'harga_jual' => $unitPrice,
                    'subtotal'   => $unitPrice * $qty,
                ]);

                // kurangi stok: varian jika ada, else produk
                if (!empty($row['flavor_id'])) {
                    $fv->stok = (int)$fv->stok - $qty;
                    if ($fv->stok < 0) $fv->stok = 0;
                    $fv->save();

                    // Jika kamu pakai trigger SQL untuk sync products.stok = SUM(flavor.stok),
                    // stok produk akan otomatis tersinkron. Jika tidak, kamu bisa update manual:
                    // $p->stok = ProductFlavor::where('product_id', $p->id)->sum('stok');
                    // $p->save();
                } else {
                    $p->stok = (int)$p->stok - $qty;
                    if ($p->stok < 0) $p->stok = 0;
                    $p->save();
                }

                $total += $unitPrice * $qty;
            }

            // set total order
            $order->update(['total_order' => $total]);

            // PENTING: Jangan panggil $order->reduceProductStock() di sini
            // karena stok sudah kita kurangi per-item/per-varian di atas.
        });

        $change = max(0, (float) $request->paid - max(0, $subtotal - $discount));
        session()->forget('pos_cart');

        return redirect()
            ->route('kasir.pos.index')
            ->with('success', 'Transaksi berhasil. Kembalian: Rp ' . number_format($change, 0, ',', '.'));
    }

    /* -----------------------------
     | Riwayat transaksi POS
     | ----------------------------- */
    public function history()
{
    $orders = Order::where('tipe_order', 'offline')
        ->orderByDesc('created_at') // pakai datetime, lebih detail
        ->paginate(20);

    return view('cashier.history', compact('orders'));
}


    /* -----------------------------
     | Laporan harian POS
     | ----------------------------- */
    public function dailyReport()
    {
        $today  = Carbon::today()->toDateString();

        $orders = Order::where('tipe_order', 'offline')
            ->whereDate('tanggal_order', $today)
            ->get();

        $total = $orders->sum('total_order');

        return view('cashier.daily', compact('orders', 'total', 'today'));
    }

    public function daily()
    {
        return $this->dailyReport();
    }
}
