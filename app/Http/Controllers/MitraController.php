<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class MitraController extends Controller
{
    // Lihat daftar mitra aktif & pending
    public function index()
    {
        $mitras = User::where('role', 'mitra')->get();
        return view('admin.mitras.index', compact('mitras'));
    }

    // Edit mitra (tampilkan form)
    public function edit($id)
    {
        $mitra = User::findOrFail($id);
        return view('admin.mitras.edit', compact('mitra'));
    }

    // Update mitra (nama, email, dst)
    public function update(Request $request, $id)
    {
        $mitra = User::findOrFail($id);
        $request->validate([
            'name'  => 'required|string|max:100',
            'email' => 'required|email'
        ]);
        $mitra->update($request->only('name', 'email'));
        return redirect()->route('mitras.index')->with('success', 'Data mitra diupdate!');
    }

    // Nonaktifkan mitra (set is_active = 0)
    public function disable($id)
    {
        $mitra = User::findOrFail($id);
        $mitra->is_active = 0;
        $mitra->save();
        return back()->with('success', 'Akun mitra dinonaktifkan.');
    }

    public function show($id)
    {
        $mitra = \App\Models\User::with('mitra')->findOrFail($id);
        return view('admin.mitras.show', compact('mitra'));
    }
}
