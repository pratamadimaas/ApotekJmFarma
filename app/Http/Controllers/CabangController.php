<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CabangController extends Controller
{
    public function index()
    {
        $cabang = Cabang::orderBy('nama_cabang')->paginate(10);
        return view('cabang.index', compact('cabang'));
    }

    public function create()
    {
        return view('cabang.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kode_cabang' => 'required|unique:cabang,kode_cabang|max:20',
            'nama_cabang' => 'required|max:100',
            'alamat' => 'nullable',
            'telepon' => 'nullable|max:20',
            'email' => 'nullable|email|max:100',
            'penanggung_jawab' => 'nullable|max:100',
            'aktif' => 'boolean'
        ], [
            'kode_cabang.required' => 'Kode cabang wajib diisi',
            'kode_cabang.unique' => 'Kode cabang sudah digunakan',
            'nama_cabang.required' => 'Nama cabang wajib diisi',
            'email.email' => 'Format email tidak valid'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Cabang::create($request->all());

        return redirect()->route('cabang.index')
            ->with('success', 'Cabang berhasil ditambahkan!');
    }

    public function show(Cabang $cabang)
    {
        return view('cabang.show', compact('cabang'));
    }

    public function edit(Cabang $cabang)
    {
        return view('cabang.edit', compact('cabang'));
    }

    public function update(Request $request, Cabang $cabang)
    {
        $validator = Validator::make($request->all(), [
            'kode_cabang' => 'required|max:20|unique:cabang,kode_cabang,' . $cabang->id,
            'nama_cabang' => 'required|max:100',
            'alamat' => 'nullable',
            'telepon' => 'nullable|max:20',
            'email' => 'nullable|email|max:100',
            'penanggung_jawab' => 'nullable|max:100',
            'aktif' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $cabang->update($request->all());

        return redirect()->route('cabang.index')
            ->with('success', 'Cabang berhasil diperbarui!');
    }

    public function destroy(Cabang $cabang)
    {
        // Check if cabang has users
        if ($cabang->users()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Tidak dapat menghapus cabang yang masih memiliki user!');
        }

        $cabang->delete();

        return redirect()->route('cabang.index')
            ->with('success', 'Cabang berhasil dihapus!');
    }

    // API untuk dropdown
    public function getAktif()
    {
        $cabang = Cabang::aktif()->orderBy('nama_cabang')->get();
        return response()->json($cabang);
    }
}