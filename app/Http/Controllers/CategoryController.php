<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('admin.category.index', compact('categories'));
    }

    public function create()
    {
        if (auth()->user()->role !== 'admin') abort(403);
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $request->validate([
            'nama_kategori' => 'required',
            'deskripsi' => 'nullable'
        ]);
        Category::create($request->only('nama_kategori', 'deskripsi'));
        return redirect()->route('categories.index')->with('success', 'Kategori ditambah!');
    }

    public function edit($id)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $category = Category::findOrFail($id);
        return view('admin.category.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        $request->validate([
            'nama_kategori' => 'required',
            'deskripsi' => 'nullable'
        ]);
        Category::findOrFail($id)->update($request->only('nama_kategori', 'deskripsi'));
        return redirect()->route('categories.index')->with('success', 'Kategori diupdate!');
    }

    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') abort(403);
        Category::destroy($id);
        return back()->with('success', 'Kategori dihapus!');
    }
}
