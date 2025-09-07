<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Order;
use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Facades\Log;



class PaymentController extends Controller
{
    // [Lihat semua status pembayaran]
    public function index(Request $request)
    {
        $query = Payment::with('order');

        // Filter optional
        if ($request->has('status_bayar') && $request->status_bayar) {
            $query->where('status_bayar', $request->status_bayar);
        }
        if ($request->has('metode_bayar') && $request->metode_bayar) {
            $query->where('metode_bayar', $request->metode_bayar);
        }

        $payments = $query->orderByDesc('id')->get();

        return view('admin.payments.index', compact('payments'));
    }

    // [Tandai manual "lunas"]
    public function setLunas($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->status_bayar = 'success';
        $payment->waktu_bayar = now();
        $payment->save();

        // Update order status juga
        if ($payment->order) {
            $previousStatus = $payment->order->status_order;
            $payment->order->status_order = 'lunas';
            $payment->order->save();

            // TIDAK kurangi stok di sini, hanya update status ke lunas
            // Stok akan dikurangi ketika admin ubah status ke "selesai"

            // Hapus snap token dari session karena pembayaran sudah lunas secara manual
            session()->forget('snap_tokens.' . $payment->order->id);
            Log::info('[Manual Payment] Snap token cleared from session for manual payment', ['order_id' => $payment->order->id]);
        }

        return back()->with('success', 'Pembayaran ditandai sebagai lunas.');
    }

    public function createTransaction(Request $request)
    {
        // Inisialisasi konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        // Data transaksi (bisa disesuaikan)
        $params = [
            'transaction_details' => [
                'order_id' => 'ORDER-' . time(),
                'gross_amount' => $request->amount ?? 100000, // nominal transaksi
            ],
            'customer_details' => [
                'first_name' => $request->first_name ?? 'User',
                'email' => $request->email ?? 'user@example.com',
                'phone' => $request->phone ?? '081234567890',
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            return response()->json(['token' => $snapToken]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function handleCallback(Request $request)
    {
        // Log awal saat callback masuk
        Log::info('[Midtrans] Callback received', $request->all());

        $serverKey = config('midtrans.server_key');

        $signatureKey = hash(
            'sha512',
            $request->order_id .
                $request->status_code .
                $request->gross_amount .
                $serverKey
        );

        if ($signatureKey !== $request->signature_key) {
            Log::warning('[Midtrans] Invalid signature', [
                'expected' => $signatureKey,
                'received' => $request->signature_key,
            ]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $order = Order::where('midtrans_order_id', $request->order_id)->first();
        if (!$order) {
            Log::error('[Midtrans] Order not found', ['order_id' => $request->order_id]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        $payment = Payment::where('order_id', $order->id)->first();
        if (!$payment) {
            Log::error('[Midtrans] Payment not found', ['order_id' => $order->id]);
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $transactionStatus = $request->transaction_status;

        Log::info('[Midtrans] Updating status', [
            'midtrans_order_id' => $request->order_id,
            'transaction_status' => $transactionStatus,
        ]);

        // Update status di order dan payment
        switch ($transactionStatus) {
            case 'capture':
            case 'settlement':
                $previousStatus = $order->status_order;
                $order->status_order = 'lunas';
                $payment->status_bayar = 'success';
                $payment->waktu_bayar = now();

                // TIDAK kurangi stok di sini, hanya update status ke lunas
                // Stok akan dikurangi ketika admin ubah status ke "selesai"

                // Hapus snap token dari session karena pembayaran sudah berhasil
                session()->forget('snap_tokens.' . $order->id);
                Log::info('[Midtrans] Snap token cleared from session for successful payment', ['order_id' => $order->id]);
                break;

            case 'pending':
                $order->status_order = 'pending';
                $payment->status_bayar = 'pending';
                break;

            case 'deny':
            case 'cancel':
            case 'expire':
                $order->status_order = 'gagal';
                $payment->status_bayar = 'gagal';
                // Hapus snap token dari session karena pembayaran gagal
                session()->forget('snap_tokens.' . $order->id);
                Log::info('[Midtrans] Snap token cleared from session for failed payment', ['order_id' => $order->id]);
                break;
        }
        $payment->payload_midtrans = json_encode($request->all());
        $order->save();
        $payment->save();

        Log::info('[Midtrans] Order & payment updated successfully', [
            'order_id' => $order->id,
            'status_order' => $order->status_order,
            'status_bayar' => $payment->status_bayar,
        ]);

        return response()->json(['message' => 'Callback handled']);
    }
}
