<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Transaction;
use Midtrans\Notification;
use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function __construct()
    {
        // Set konfigurasi Midtrans
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

public function notificationHandler(Request $request)
{
    try {
        // Inisialisasi notifikasi dari Midtrans
        $notif = new \Midtrans\Notification();

        $transaction = $notif->transaction_status;
        $midtrans_order_id = $notif->order_id;
        $payment_type = $notif->payment_type ?? null;
        $fraud = $notif->fraud_status ?? null;

        // Temukan order berdasarkan midtrans_order_id
        $order = \App\Models\Order::where('midtrans_order_id', $midtrans_order_id)->first();
        if (!$order) {
            \Log::error('[Midtrans] Order not found', ['midtrans_order_id' => $midtrans_order_id]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Temukan payment terkait order (jika ada)
        $payment = \App\Models\Payment::where('order_id', $order->id)->first();

        // Proses status dari Midtrans
        if ($transaction == 'capture') {
            if ($payment_type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $order->status_order = 'pending';
                    if ($payment) {
                        $payment->status_bayar = 'pending';
                        $payment->save();
                    }
                } else {
                    $order->status_order = 'lunas';
                    if ($payment) {
                        $payment->status_bayar = 'success';
                        $payment->waktu_bayar = now();
                        $payment->save();
                    }
                }
            }
        } elseif ($transaction == 'settlement') {
            $order->status_order = 'lunas';
            if ($payment) {
                $payment->status_bayar = 'success';
                $payment->waktu_bayar = now();
                $payment->save();
            }
        } elseif ($transaction == 'pending') {
            $order->status_order = 'pending';
            if ($payment) {
                $payment->status_bayar = 'pending';
                $payment->save();
            }
        } elseif ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
            $order->status_order = 'gagal';
            if ($payment) {
                $payment->status_bayar = 'gagal';
                $payment->save();
            }
        } else {
            // Status tidak dikenal, log saja
            \Log::warning('[Midtrans] Unknown transaction status', [
                'transaction' => $transaction,
                'order_id' => $midtrans_order_id,
            ]);
        }

        // Simpan perubahan pada order
        $order->save();

        // Log sukses
        \Log::info('[Midtrans] Notifikasi diproses sukses', [
            'order_id' => $order->id,
            'midtrans_order_id' => $midtrans_order_id,
            'status_order' => $order->status_order,
            'payment_status' => $payment->status_bayar ?? null
        ]);

        return response()->json([
            'message' => 'Notifikasi berhasil diproses',
            'status' => $order->status_order,
            'order_id' => $midtrans_order_id,
        ]);
    } catch (\Exception $e) {
        \Log::error('[Midtrans] Error notificationHandler', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json(['message' => 'Error notification'], 500);
    }
}

    // Validasi status pesanan sebelum lanjut bayar
    public function validateOrderStatusBeforePay($midtrans_order_id)
    {
        try {
            $status = Transaction::status($midtrans_order_id);
            $transaction_status = $status->transaction_status ?? null;

            if ($transaction_status !== 'pending') {
                return redirect('/keranjang')->with('error', 'Pesanan sudah tidak bisa dibayar. Silakan checkout ulang.');
            }
        } catch (\Exception $e) {
            return redirect('/keranjang')->with('error', 'Error validasi status: ' . $e->getMessage());
        }
    }

    // Fungsi bayar ulang
    public function payAgain($midtrans_order_id)
    {
        $order = Order::where('midtrans_order_id', $midtrans_order_id)->firstOrFail();

        try {
            $status = Transaction::status($midtrans_order_id);

            if (($status->transaction_status ?? null) !== 'pending') {
                return redirect()->route('home.keranjang')->with('error', 'Transaksi tidak bisa dibayar ulang.');
            }

            $user = User::find($order->user_id);

            $params = [
                'transaction_details' => [
                    'order_id' => $order->midtrans_order_id,
                    'gross_amount' => $order->total_order,
                ],
                'customer_details' => [
                    'first_name' => $user->name ?? 'Customer',
                    'email' => $user->email ?? 'customer@example.com',
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            return view('home.snap_checkout', compact('snapToken', 'order'));
        } catch (\Exception $e) {
            return redirect()->route('home.keranjang')->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
