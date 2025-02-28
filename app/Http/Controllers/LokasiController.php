<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lokasi;

class LokasiController extends Controller
{
    /**
     * menampilkan seluruh lokasi.
     */
    public function index()
    {
        $lokasi = Lokasi::all();
        return response()->json($lokasi);
    }

    /**
     * Store a new lokasi in the database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_gedung' => 'required|string|max:50',
        ]);

        $lokasi = Lokasi::create($validated);

        return response()->json([
            'message' => 'Lokasi created successfully',
            'lokasi' => $lokasi,
        ], 201);
    }

    /**
     * menampilkan lokasi sesuai id.
     */
    public function show($id)
    {
        $lokasi = Lokasi::findOrFail($id);
        return response()->json($lokasi);
    }

    /**
     * Update lokasi sesuai id.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_gedung' => 'required|string|max:50',
        ]);

        $lokasi = Lokasi::findOrFail($id);
        $lokasi->update($validated);

        return response()->json([
            'message' => 'Lokasi updated successfully',
            'lokasi' => $lokasi,
        ]);
    }

    /**
     * Hapus lokasi sesuai id.
     */
    public function destroy($id)
    {
        $lokasi = Lokasi::findOrFail($id);
        $lokasi->delete();

        return response()->json(['message' => 'Lokasi deleted successfully']);
    }
    
    public function totalLokasi()
    {
        $total = Lokasi::count();
        return response()->json(['total_lokasi' => $total], 200);
    }
}