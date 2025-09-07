<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $r)
    {
        // Validasi utama
        $r->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'role'     => 'required|in:admin,supplier,mitra,customer,kasir',
        ]);

        // Validasi tambahan jika role mitra
        if ($r->role === 'mitra') {
            $r->validate([
                'nama_usaha'   => 'required|string|max:100',
                'mitra_no_hp'  => 'required|string|max:30',
                'mitra_alamat' => 'required|string|max:255',
                'foto'         => 'nullable|image|max:2048',
            ]);
        }
        // Validasi tambahan jika role supplier
        if ($r->role === 'supplier') {
            $r->validate([
                'perusahaan'     => 'required|string|max:100',
                'supplier_no_hp' => 'required|string|max:30',
                'supplier_alamat' => 'required|string|max:255',
                'foto'           => 'nullable|image|max:2048',
            ]);
        }
        // Validasi tambahan jika role customer
        if ($r->role === 'customer') {
            $r->validate([
                'customer_alamat' => 'required|string|max:255',
                'customer_no_hp'  => 'required|string|max:30',
                'foto'            => 'nullable|image|max:2048',
            ]);
        }

        // Simpan user utama
        $data = $r->only('name', 'email', 'password', 'role');
        $data['password'] = Hash::make($r->password);
        $data['is_active'] = ($data['role'] === 'customer') ? 1 : 0;
        $user = User::create($data);

        // Upload foto jika ada
        $fotoPath = null;
        if ($r->hasFile('foto')) {
            $fotoPath = $r->file('foto')->store('foto_user', 'public');
        }

        // Insert data sesuai role
        if ($data['role'] === 'supplier') {
            $user->supplier()->create([
                'nama_supplier' => $r->name, // OTOMATIS dari Nama Lengkap!
                'perusahaan'    => $r->perusahaan,
                'no_hp'         => $r->supplier_no_hp,
                'alamat'        => $r->supplier_alamat,
                'foto'          => $fotoPath,
            ]);
        } elseif ($data['role'] === 'mitra') {
            $user->mitra()->create([
                'nama_mitra'  => $r->name, // OTOMATIS dari Nama Lengkap!
                'nama_usaha'  => $r->nama_usaha,
                'no_hp'       => $r->mitra_no_hp,
                'alamat'      => $r->mitra_alamat,
                'foto'        => $fotoPath,
            ]);
        } elseif ($data['role'] === 'customer') {
            $user->customer()->create([
                'nama_customer' => $r->name, // OTOMATIS dari Nama Lengkap!
                'alamat'        => $r->customer_alamat,
                'no_hp'         => $r->customer_no_hp,
                'foto'          => $fotoPath,
            ]);
        }

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    }

    public function login(Request $r)
    {
        $credentials = $r->only('email', 'password');
        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if ($user->is_active == 0) {
                Auth::logout();
                return back()->withErrors(['Akun Anda belum diverifikasi!']);
            }
            // Redirect sesuai role
            if ($user->role == 'admin')     return redirect()->route('dashboard.admin');
            if ($user->role == 'supplier')  return redirect()->route('supplier.dashboard');
            if ($user->role == 'kasir')    return redirect()->route('kasir.dashboard');
            if ($user->role == 'mitra')     return redirect()->route('home.katalog');
            if ($user->role == 'customer')  return redirect()->route('home.katalog');
            return redirect('/');
        }
        return back()->withErrors(['Login gagal! Email atau password salah.']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function verifikasi()
    {
        $penggunas = User::whereIn('role', ['mitra', 'supplier'])
            ->where('is_active', 0)
            ->get();
        return view('admin.users.verifikasi', compact('penggunas'));
    }

    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->is_active = 1;
        $user->save();
        return redirect()->route('users.verifikasi')->with('success', 'Akun berhasil diaktifkan!');
    }
}
