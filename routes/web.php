<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    CategoryController,
    ProductController,
    ProductImageController,
    SupplierController,
    MitraController,
    CustomerController,
    LaporanController,
    PurchaseController,
    OrderController,
    PaymentController,
    MidtransController,
    SupplierDashboardController,
    SupplierProductController,
    HomeController,
    ProfileController,
    AdminDashboardController,
    CashierDashboardController,
    PosController
};
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Hash;

// =====================
// KATALOG & KERANJANG (Umum)
// =====================
Route::get('/', [HomeController::class, 'katalog'])->name('home.katalog');
Route::get('/keranjang', [HomeController::class, 'keranjang'])->name('home.keranjang');
Route::post('/keranjang/tambah/{id}', [HomeController::class, 'tambahKeranjang'])->name('home.keranjang.tambah');
Route::post('/keranjang/hapus/{id}', [HomeController::class, 'hapusKeranjang'])->name('home.keranjang.hapus');
Route::post('/keranjang/update/{id}', [HomeController::class, 'updateKeranjang'])->name('home.keranjang.update');

Route::middleware(['auth'])->group(function () {
    Route::post('/checkout', [HomeController::class, 'checkout'])->name('home.checkout');
    // ...route lain yg butuh login
});
Route::get('/checkout/success', function () {
    return view('home.checkout_success');
})->name('home.checkout.success');
Route::get('/checkout/pending', function () {
    return view('home.checkout_pending');
})->name('home.checkout.pending');
Route::get('/checkout/failed', function () {
    return view('home.checkout_failed');
})->name('home.checkout.failed');

// =====================
// AUTH (register, login, logout)
// =====================
Route::get('register', function () {
    return view('auth.register');
})->name('register');
Route::post('register', [AuthController::class, 'register']);
Route::get('login', function () {
    return view('auth.login');
})->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// =====================
// USER AREA (auth required: profile, orders, edit profile)
// =====================
Route::middleware('auth')->group(function () {
    Route::get('/profile', function () {
        return view('home.profile');
    })->name('home.profile');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/orders', [HomeController::class, 'pesananSaya'])->name('home.orders');
    Route::prefix('myorders')->name('home.myorders.')->group(function () {
        Route::get('/', [HomeController::class, 'pesananSaya'])->name('index');
        Route::get('/{id}', [HomeController::class, 'pesananDetail'])->name('detail');
        Route::post('/{id}/lanjutkan-pembayaran', [HomeController::class, 'lanjutkanPembayaran'])->name('lanjutkan_pembayaran');
        Route::post('/{id}/cancel', [HomeController::class, 'cancelOrder'])->name('cancel');
    });
    Route::post('/beli-sekarang/{id}', [HomeController::class, 'beliSekarang'])->name('home.beli.sekarang');
});

// =====================
// DASHBOARD (dinamis redirect berdasarkan role)
// =====================
Route::get('/dashboard', function () {
    if (!auth()->check()) return redirect()->route('login');
    $role = auth()->user()->role;
    switch ($role) {
        case 'admin':
            return redirect()->route('dashboard.admin');
        case 'supplier':
            return redirect()->route('supplier.dashboard');
        case 'kasir':
            return redirect()->route('kasir.dashboard');
        case 'mitra':
        case 'customer':
            return redirect()->route('home.katalog');
        default:
            abort(403, 'Role tidak dikenal');
    }
});
Route::get('/dashboard/admin', [AdminDashboardController::class, 'index'])->name('dashboard.admin');

// =====================
// VERIFIKASI PENGGUNA BARU (admin)
// =====================
Route::get('users/verifikasi', [AuthController::class, 'verifikasi'])->name('users.verifikasi');
Route::post('users/{id}/approve', [AuthController::class, 'approve'])->name('users.approve');

// =====================
// KATEGORI PRODUK (admin)
// =====================
Route::resource('categories', CategoryController::class)->except(['show']);
Route::post('/products/check-kode', [ProductController::class, 'checkKode'])->name('products.checkKode');

// =====================
// PRODUK (admin)
// =====================
Route::resource('products', ProductController::class);


Route::post('products/{id}/approve-supplier', [ProductController::class, 'approveSupplierProduct'])
    ->name('products.approveSupplierProduct');
Route::post('products/receive-from-supplier', [ProductController::class, 'receiveFromSupplier'])->name('products.receiveFromSupplier');
Route::post('products/{productId}/images', [ProductImageController::class, 'store'])->name('products.images.store');
Route::delete('product-images/{id}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
Route::get('admin/products/{id}/{notificationId?}', [ProductController::class, 'show'])->name('admin.products.show');

// =====================
// SUPPLIER (admin-only)
// =====================
Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
Route::get('suppliers/{id}', [SupplierController::class, 'show'])->name('suppliers.show');
Route::post('suppliers/{id}/verify', [SupplierController::class, 'verify'])->name('suppliers.verify');
Route::post('suppliers/{id}/nonaktif', [SupplierController::class, 'nonaktif'])->name('suppliers.nonaktif');
Route::get('suppliers/{id}/products', [SupplierController::class, 'products'])->name('suppliers.products');
// Route untuk menambah stok produk dari supplier ke toko (ADMIN beli dari supplier)
Route::post('suppliers/{supplier_id}/order-product/{product_id}', [SupplierController::class, 'orderProduct'])->name('suppliers.orderProduct');

// =====================
// MITRA (admin-only)
// =====================
Route::resource('mitras', MitraController::class)->only(['index', 'edit', 'update', 'show']);
Route::post('mitras/{id}/disable', [MitraController::class, 'disable'])->name('mitras.disable');

// =====================
// CUSTOMER (admin-only)
// =====================
Route::resource('customers', CustomerController::class)->only(['index', 'show']);
Route::post('customers/{id}/disable', [CustomerController::class, 'disable'])->name('customers.disable');

// =====================
// PEMBELIAN KE SUPPLIER (admin)
// =====================
Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);

// =====================
// PENJUALAN / ORDER (admin/kasir)
// =====================
Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);
Route::post('orders/{midtrans_order_id}/pay', [OrderController::class, 'pay'])->name('orders.pay');
Route::post('orders/{midtrans_order_id}/kirim', [OrderController::class, 'kirim'])->name('orders.kirim');
Route::patch('orders/{midtrans_order_id}/ubah-status', [OrderController::class, 'ubahStatus'])->name('orders.ubahStatus');

// =====================
// PEMBAYARAN (admin)
// =====================
Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
Route::post('payments/{id}/set-lunas', [PaymentController::class, 'setLunas'])->name('payments.setLunas');
Route::post('payments/create', [PaymentController::class, 'createTransaction'])->name('payments.create');
Route::post('payments/callback', [PaymentController::class, 'handleCallback'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('payments.callback');

// =====================
// LAPORAN (admin)
// =====================
Route::prefix('laporan')->name('laporan.')->group(function () {
    Route::get('penjualan', [LaporanController::class, 'penjualan'])->name('penjualan');
    Route::get('produk-terlaris', [LaporanController::class, 'produkTerlaris'])->name('produk_terlaris');
    Route::get('pembelian', [LaporanController::class, 'pembelian'])->name('pembelian');

    // // Detail & Print per transaksi
    // Route::get('penjualan/{id}', [LaporanController::class, 'penjualanDetail'])->name('penjualan.detail');
    // Route::get('penjualan/{id}/print', [LaporanController::class, 'penjualanDetailPdf'])->name('penjualan.detail_pdf');

    // PDF exports keseluruhan
    Route::get('penjualan/pdf', [LaporanController::class, 'penjualanPdf'])->name('penjualan_pdf');
    Route::get('pembelian/pdf', [LaporanController::class, 'pembelianPdf'])->name('pembelian_pdf');
    Route::get('produk-terlaris/pdf', [LaporanController::class, 'produkTerlarisPdf'])->name('produk_terlaris_pdf');
});


// =====================
// SUPPLIER AREA (role supplier saja)
// =====================
Route::middleware(['auth', 'role:supplier'])->prefix('supplier')->name('supplier.')->group(function () {
    Route::get('/dashboard', [SupplierDashboardController::class, 'index'])->name('dashboard');
    Route::get('products', [SupplierProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [SupplierProductController::class, 'create'])->name('products.create');
    Route::post('products', [SupplierProductController::class, 'store'])->name('products.store');
    Route::get('products-toko', [SupplierProductController::class, 'produkToko'])->name('products.toko');
    Route::get('products-toko/{id}/tawarkan', [SupplierProductController::class, 'tawarkanAlternatifForm'])->name('products.tawarkan');
    Route::post('products-toko/{id}/tawarkan', [SupplierProductController::class, 'tawarkanAlternatif'])->name('products.tawarkan.store');
    Route::get('products/{product}/edit', [SupplierProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{product}', [SupplierProductController::class, 'update'])->name('products.update');
    Route::delete('products/{product}', [SupplierProductController::class, 'destroy'])->name('products.destroy');
    Route::post('products/{id}/offer-to-store', [SupplierProductController::class, 'offerToStore'])->name('products.offerToStore');
});

Route::middleware(['auth', 'role:kasir'])
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function () {

        // ⬇⬇ INI yang diminta redirect: kasir.dashboard
        Route::get('/dashboard', [CashierDashboardController::class, 'index'])
            ->name('dashboard');

        // POS
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos/add', [PosController::class, 'add'])->name('pos.add');
        Route::post('/pos/update', [PosController::class, 'update'])->name('pos.update');
        Route::post('/pos/remove', [PosController::class, 'remove'])->name('pos.remove');
        Route::post('/pos/clear', [PosController::class, 'clear'])->name('pos.clear');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

        // Riwayat & Laporan
        Route::get('/riwayat', [PosController::class, 'history'])->name('history');
        Route::get('/laporan-harian', [PosController::class, 'dailyReport'])->name('daily');
    });

// =====================
// DETAIL PRODUK (Umum)
// =====================
Route::get('/produk/{id}', [HomeController::class, 'produkDetail'])->name('home.produk.detail');

// =====================
// MIDTRANS CALLBACK/NOTIFICATION
// =====================
Route::post('/midtrans/notification', [MidtransController::class, 'notificationHandler'])
    ->name('midtrans.notification')
    ->withoutMiddleware([VerifyCsrfToken::class]);

// =====================
// FALLBACK - 404 custom
// =====================
Route::fallback(function () {
    abort(404, 'Halaman tidak ditemukan.');
});
