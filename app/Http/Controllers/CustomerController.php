<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class CustomerController extends Controller
{
    // Lihat daftar customer
    public function index()
    {
        $customers = User::where('role', 'customer')->get();
        return view('admin.customers.index', compact('customers'));
    }

    public function show($id)
    {
        $customer = \App\Models\User::with('customer')->findOrFail($id);
        return view('admin.customers.show', compact('customer'));
    }

    public function disable($id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->is_active = 0;
        $user->save();
        return back()->with('success', 'Customer berhasil dinonaktifkan!');
    }
}
