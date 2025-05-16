<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lokasi;
use Illuminate\Support\Facades\Storage;

class LokasiController extends Controller
{
    /**
     * Display all lokasi.
     */
    public function index()
    {
        $lokasi = Lokasi::all();
        return response()->json($lokasi);
    }

    /**
     * Store a new lokasi in the database with an image.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_gedung' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('lokasi_images', 'public');
            $validated['image'] = $imagePath;
        }

        $lokasi = Lokasi::create($validated);

        return response()->json([
            'message' => 'Lokasi created successfully',
            'lokasi' => $lokasi,
        ], 201);
    }

    /**
     * Show lokasi by id.
     */
    public function show($id)
    {
        $lokasi = Lokasi::findOrFail($id);
        return response()->json($lokasi);
    }

    /**
     * Update lokasi with an image.
     */
    public function update(Request $request, $id)
    {
        $lokasi = Lokasi::findOrFail($id);

        $validated = $request->validate([
            'nama_gedung' => 'required|string|max:50',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($lokasi->image) {
                Storage::disk('public')->delete($lokasi->image);
            }
            $imagePath = $request->file('image')->store('lokasi_images', 'public');
            $validated['image'] = $imagePath;
        }

        $lokasi->update($validated);

        return response()->json([
            'message' => 'Lokasi updated successfully',
            'lokasi' => $lokasi,
        ]);
    }

    /**
     * Delete lokasi by id.
     */
    public function destroy($id)
    {
        $lokasi = Lokasi::findOrFail($id);

        if ($lokasi->image) {
            Storage::disk('public')->delete($lokasi->image);
        }

        $lokasi->delete();

        return response()->json(['message' => 'Lokasi deleted successfully']);
    }

    /**
     * Count total lokasi.
     */
    public function totalLokasi()
    {
        $total = Lokasi::count();
        return response()->json(['total_lokasi' => $total], 200);
    }

    public function destroyAll()
    {
        Lokasi::truncate();

        return response()->json([
            'message' => 'All Kode Barang records deleted successfully'
        ]);
    }
}
