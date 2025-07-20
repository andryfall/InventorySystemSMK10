<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeminjamanAset;
use App\Models\AssetItem;
use Carbon\Carbon;

class PeminjamanAsetController extends Controller
{
    public function index()
    {
        $peminjamans = PeminjamanAset::with('assetItem.kodeBarang')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($peminjaman) {
                return [
                    'id' => $peminjaman->id,
                    'asset_item_id' => $peminjaman->asset_item_id,
                    'nama_peminjam' => $peminjaman->nama_peminjam,
                    'volume' => $peminjaman->volume,
                    'keperluan' => $peminjaman->keperluan,
                    'status' => $peminjaman->status,
                    'tanggal_pinjam' => $peminjaman->tanggal_pinjam,
                    'tanggal_kembali' => $peminjaman->tanggal_kembali,
                    'created_at' => $peminjaman->created_at,
                    'updated_at' => $peminjaman->updated_at,
                    'nama_barang' => $peminjaman->assetItem->kodeBarang->uraian ?? null,
                ];
            });

        return response()->json($peminjamans);
    }

    public function store(Request $request, $id)
    {
        $request->validate([
            'nama_peminjam' => 'required|string|max:255',
            'volume' => 'required|integer|min:1',
            'keperluan' => 'nullable|string',
            'jenis' => 'required|in:pinjam,ambil',
        ]);

        $assetItem = AssetItem::findOrFail($id);

        if ($request->volume > $assetItem->jumlah) {
            return response()->json(['error' => 'Jumlah yang diminta melebihi stok yang tersedia.'], 422);
        }

        $assetItem->jumlah -= $request->volume;
        $assetItem->save();

        $status = $request->jenis === 'pinjam' ? 'dipinjam' : 'diambil';

        $peminjaman = PeminjamanAset::create([
            'asset_item_id' => $assetItem->aset,
            'nama_peminjam' => $request->nama_peminjam,
            'volume' => $request->volume,
            'keperluan' => $request->keperluan,
            'tanggal_pinjam' => Carbon::now(),
            'status' => $status,
        ]);

        return response()->json($peminjaman);
    }


    public function show($id)
    {
        $peminjaman = PeminjamanAset::with('assetItem')->findOrFail($id);
        return response()->json($peminjaman);
    }

    public function update(Request $request, $id)
    {
        $peminjaman = PeminjamanAset::findOrFail($id);

        $request->validate([
            'nama_peminjam' => 'sometimes|required|string|max:255',
            'volume' => 'sometimes|required|integer|min:1',
            'keperluan' => 'nullable|string',
        ]);

        $peminjaman->update($request->only(['nama_peminjam', 'volume', 'keperluan']));
        return response()->json($peminjaman);
    }

    public function destroy($id)
    {
        $peminjaman = PeminjamanAset::findOrFail($id);

        if ($peminjaman->status === 'dipinjam' || $peminjaman->status === 'diambil') {
            $assetItem = AssetItem::find($peminjaman->asset_item_id);

            if (!$assetItem) {
                return response()->json(['error' => 'Asset item tidak ditemukan.'], 404);
            }

            $assetItem->jumlah += $peminjaman->volume;
            $assetItem->save();
        }

        $peminjaman->delete();

        return response()->json(['message' => 'Data peminjaman dihapus.']);
    }

    public function kembalikan($id)
    {
        $peminjaman = PeminjamanAset::findOrFail($id);

        if ($peminjaman->status === 'dipinjam') {
            $assetItem = AssetItem::find($peminjaman->asset_item_id);

            if (!$assetItem) {
                return response()->json(['error' => 'Asset item tidak ditemukan.'], 404);
            }

            $assetItem->jumlah += $peminjaman->volume;
            $assetItem->save();
        }

        $peminjaman->status = 'terkembalikan';
        $peminjaman->tanggal_kembali = Carbon::now();
        $peminjaman->save();

        return response()->json([
            'message' => 'Peminjaman berhasil dikembalikan.',
            'data' => $peminjaman,
        ]);
    }

    public function destroyAll()
    {
        PeminjamanAset::truncate();

        return response()->json([
            'message' => 'Seluruh data peminjaman aset telah dihapus'
        ]);
    }

}
