<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AssetItem;
use App\Models\KodeBarang;
use App\Models\Lokasi;

class AssetItemController extends Controller
{
    public function index()
    {
        return response()->json(AssetItem::with(['kodeBarang', 'lokasi'])->get());
    }

    public function show($id)
    {
        $assetItem = AssetItem::with(['kodeBarang', 'lokasi'])->find($id);

        if (!$assetItem) {
            return response()->json(['message' => 'Asset item not found'], 404);
        }

        return response()->json($assetItem);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang_id' => 'required|exists:kode_barang,id',
            'lokasi_id' => 'required|exists:lokasi,id',
            'keterangan' => 'required|string|max:250',
            'uraian' => 'required|string|max:250',
            'satuan' => 'required|string|max:8',
            'jumlah' => 'required|integer|min:1',
            'harga' => 'required|integer|min:0',
        ]);

        $assetItem = AssetItem::create($validated);

        return response()->json([
            'message' => 'Asset item created successfully',
            'asset_item' => $assetItem,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $assetItem = AssetItem::find($id);

        if (!$assetItem) {
            return response()->json(['message' => 'Asset item not found'], 404);
        }

        $validated = $request->validate([
            'kode_barang_id' => 'exists:kode_barang,id',
            'lokasi_id' => 'exists:lokasi,id',
            'keterangan' => 'string|max:250',
            'uraian' => 'string|max:250',
            'satuan' => 'string|max:8',
            'jumlah' => 'integer|min:1',
            'harga' => 'integer|min:0',
        ]);

        $assetItem->update($validated);

        return response()->json([
            'message' => 'Asset item updated successfully',
            'asset_item' => $assetItem,
        ]);
    }

    public function destroy($id)
    {
        $assetItem = AssetItem::find($id);

        if (!$assetItem) {
            return response()->json(['message' => 'Asset item not found'], 404);
        }

        $assetItem->delete();

        return response()->json(['message' => 'Asset item deleted successfully']);
    }
}
