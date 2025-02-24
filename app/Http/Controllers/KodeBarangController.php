<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KodeBarang;

class KodeBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(KodeBarang::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang' => 'required|string|max:250',
            'jenis_barang' => 'required|string|max:250',
            'merk' => 'required|string|max:250',
        ]);

        $kodeBarang = KodeBarang::create($validated);

        return response()->json([
            'message' => 'Kode Barang created successfully',
            'kode_barang' => $kodeBarang,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kodeBarang = KodeBarang::find($id);

        if (!$kodeBarang) {
            return response()->json(['message' => 'Kode Barang not found'], 404);
        }

        return response()->json($kodeBarang, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $kodeBarang = KodeBarang::find($id);

        if (!$kodeBarang) {
            return response()->json(['message' => 'Kode Barang not found'], 404);
        }

        $validated = $request->validate([
            'nama_barang' => 'sometimes|string|max:250',
            'jenis_barang' => 'sometimes|string|max:250',
            'merk' => 'sometimes|string|max:250',
        ]);

        $kodeBarang->update($validated);

        return response()->json([
            'message' => 'Kode Barang updated successfully',
            'kode_barang' => $kodeBarang,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $kodeBarang = KodeBarang::find($id);

        if (!$kodeBarang) {
            return response()->json(['message' => 'Kode Barang not found'], 404);
        }

        $kodeBarang->delete();

        return response()->json(['message' => 'Kode Barang deleted successfully'], 200);
    }
}