<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // Halaman List Supplier
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_supplier', 'LIKE', "%{$search}%")
                  ->orWhere('telepon', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $suppliers = $query->orderBy('nama_supplier', 'asc')->paginate(20);

        return view('pages.supplier.index', compact('suppliers'));
    }

    // Halaman Form Tambah Supplier
    public function create()
    {
        return view('pages.supplier.create');
    }

    // Proses Simpan Supplier
    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'telepon' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'required|string',
        ]);

        Supplier::create([
            'nama_supplier' => $request->nama_supplier,
            'telepon' => $request->telepon,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'kontak_person' => $request->kontak_person,
            'keterangan' => $request->keterangan
        ]);

        return redirect()->route('supplier.index')
                       ->with('success', 'Supplier berhasil ditambahkan!');
    }

    // Detail Supplier
    public function show($id)
    {
        $supplier = Supplier::with(['pembelian' => function($query) {
            $query->orderBy('tanggal', 'desc')->limit(10);
        }])->findOrFail($id);
        
        return view('pages.supplier.show', compact('supplier'));
    }

    // Halaman Edit Supplier
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        
        return view('pages.supplier.edit', compact('supplier'));
    }

    // Proses Update Supplier
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_supplier' => 'required|string|max:255',
            'telepon' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'alamat' => 'required|string',
        ]);

        $supplier = Supplier::findOrFail($id);
        
        $supplier->update([
            'nama_supplier' => $request->nama_supplier,
            'telepon' => $request->telepon,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'kontak_person' => $request->kontak_person,
            'keterangan' => $request->keterangan
        ]);

        return redirect()->route('supplier.index')
                       ->with('success', 'Supplier berhasil diupdate!');
    }

    // Hapus Supplier
    public function destroy($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            
            // Cek apakah supplier sudah pernah digunakan di transaksi
            if ($supplier->pembelian()->count() > 0) {
                return back()->with('error', 'Supplier tidak bisa dihapus karena sudah ada transaksi pembelian!');
            }

            $supplier->delete();

            return redirect()->route('supplier.index')
                           ->with('success', 'Supplier berhasil dihapus!');

        } catch (\Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}