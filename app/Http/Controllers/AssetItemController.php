<?php

namespace App\Http\Controllers;

use App\Models\AssetItem;
use Illuminate\Http\Request;

class AssetItemController extends Controller
{
    public function index()
    {
        $assets = AssetItem::with(['kodeBarang.parent.parent'])->get();
    
        return response()->json([
            'data' => $assets->map(function ($asset) {
                return [
                    'id' => $asset->aset,
                    'kode_barang' => $asset->kodeBarang->kode,
                    'nama_barang' => $asset->kodeBarang->uraian,
                    'nama_rekening_aset' => optional($asset->kodeBarang->parent)->uraian ?? 'Unknown',
                    'kode_rekening_aset' => optional(optional($asset->kodeBarang->parent)->parent)->kode 
                        ? rtrim(optional(optional($asset->kodeBarang->parent)->parent)->kode, '.') 
                        : 'Unknown',
                    'lokasi' => $asset->lokasi->nama_gedung,
                    'merk_barang' => $asset->merk_barang,
                    'satuan' => $asset->satuan,
                    'jumlah' => $asset->jumlah,
                    'harga' => $asset->harga,
                    'tanggal_pembelian' => $asset->tanggal_pembelian,
                    'no_spk_faktur_kuitansi' => $asset->no_spk_faktur_kuitansi,
                    'kode_rekening_belanja' => $asset->kode_rekening_belanja,
                    'no_bast' => $asset->no_bast,
                    'umur_ekonomis' => $asset->umur_ekonomis,
                    'nilai_perolehan' => $asset->nilai_perolehan,
                    'beban_penyusutan' => $asset->beban_penyusutan,
                    'kondisi' => $asset->kondisi,
                    'created_at' => $asset->created_at,
                    'updated_at' => $asset->updated_at,
                ];
            })
        ]);
    }
    
    

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|exists:kode_barang,kode',
            'nama_gedung' => 'required|exists:lokasi,nama_gedung',
            'merk_barang' => 'required|string|max:250',
            'satuan' => 'required|string|max:8',
            'jumlah' => 'required|integer',
            'harga' => 'required|integer',
            'tanggal_pembelian' => 'required|date',
            'no_spk_faktur_kuitansi' => 'required|string|max:50',
            'kode_rekening_belanja' => 'required|string|max:50',
            'no_bast' => 'required|string|max:50',
            'umur_ekonomis' => 'required|integer',
            'nilai_perolehan' => 'required|integer',
            'beban_penyusutan' => 'required|integer',
            'kondisi' => 'required|string|max:50',
        ]);
    
        $kodeBarang = \App\Models\KodeBarang::where('kode', $validated['kode'])->firstOrFail();
        
        $validated['kode_barang_id'] = $kodeBarang->id;
    
        unset($validated['kode']);

        $lokasi = \App\Models\Lokasi::where('nama_gedung', $validated['nama_gedung'])->firstOrFail();
        
        $validated['lokasi_id'] = $lokasi->id;
    
        unset($validated['nama_gedung']);
    
        $asset = AssetItem::create($validated);
    
        return response()->json(['message' => 'Asset created successfully', 'data' => $asset], 201);
    }

    public function show($id)
    {
        $asset = AssetItem::with(['kodeBarang.parent.parent'])->findOrFail($id);
    
        return response()->json([
            'data' => [
                'id' => $asset->aset,
                'kode_barang' => $asset->kodeBarang->kode,
                'nama_barang' => $asset->kodeBarang->uraian,
                'nama_rekening_aset' => optional($asset->kodeBarang->parent)->uraian ?? 'Unknown',
                'kode_rekening_aset' => optional(optional($asset->kodeBarang->parent)->parent)->kode 
                    ? rtrim(optional(optional($asset->kodeBarang->parent)->parent)->kode, '.') 
                    : 'Unknown',
                'lokasi' => $asset->lokasi->nama_gedung,
                'merk_barang' => $asset->merk_barang,
                'satuan' => $asset->satuan,
                'jumlah' => $asset->jumlah,
                'harga' => $asset->harga,
                'tanggal_pembelian' => $asset->tanggal_pembelian,
                'no_spk_faktur_kuitansi' => $asset->no_spk_faktur_kuitansi,
                'kode_rekening_belanja' => $asset->kode_rekening_belanja,
                'no_bast' => $asset->no_bast,
                'umur_ekonomis' => $asset->umur_ekonomis,
                'nilai_perolehan' => $asset->nilai_perolehan,
                'beban_penyusutan' => $asset->beban_penyusutan,
                'kondisi' => $asset->kondisi,
                'created_at' => $asset->created_at,
                'updated_at' => $asset->updated_at,
            ]
        ]);
    }
    


    public function update(Request $request, $id)
    {
        $asset = AssetItem::findOrFail($id);
    
        $validated = $request->validate([
            'kode' => 'sometimes|string|exists:kode_barang,kode',
            'nama_gedung' => 'required|exists:lokasi,nama_gedung',
            'merk_barang' => 'sometimes|string|max:250',
            'satuan' => 'sometimes|string|max:8',
            'jumlah' => 'sometimes|integer',
            'harga' => 'sometimes|integer',
            'tanggal_pembelian' => 'sometimes|date',
            'no_spk_faktur_kuitansi' => 'sometimes|string|max:50',
            'kode_rekening_belanja' => 'sometimes|string|max:50',
            'no_bast' => 'sometimes|string|max:50',
            'umur_ekonomis' => 'sometimes|integer',
            'nilai_perolehan' => 'sometimes|integer',
            'beban_penyusutan' => 'sometimes|integer',
            'kondisi' => 'required|string|max:50',
        ]);
    
        if ($request->has('kode')) {
            $kodeBarang = \App\Models\KodeBarang::where('kode', $validated['kode'])->firstOrFail();
            $validated['kode_barang_id'] = $kodeBarang->id;
    
            unset($validated['kode']);
        }

        $lokasi = \App\Models\Lokasi::where('nama_gedung', $validated['nama_gedung'])->firstOrFail();
        
        $validated['lokasi_id'] = $lokasi->id;
    
        unset($validated['nama_gedung']);
    
        $asset->update($validated);
    
        return response()->json(['message' => 'Asset updated successfully', 'data' => $asset], 200);
    }
    

    public function destroy($id)
    {
        $asset = AssetItem::findOrFail($id);
        $asset->delete();
        return response()->json(['message' => 'Asset deleted successfully']);
    }

    public function totalAssets()
    {
        $total = AssetItem::count();
        return response()->json(['total_assets' => $total], 200);
    }

    public function totalHargaCurrentMonthYear()
    {
        $totalMonth = AssetItem::whereYear('tanggal_pembelian', now()->year)
            ->whereMonth('tanggal_pembelian', now()->month)
            ->sum('harga');

        $totalYear = AssetItem::whereYear('tanggal_pembelian', now()->year)
            ->sum('harga');

        return response()->json([
            'total_harga_current_month' => $totalMonth,
            'total_harga_current_year' => $totalYear
        ], 200);
    }

    public function totalHargaByYear($year)
    {
        $totalYear = AssetItem::whereYear('tanggal_pembelian', $year)->sum('harga');
    
        $monthlyTotals = AssetItem::selectRaw('EXTRACT(MONTH FROM tanggal_pembelian) as month, SUM(harga) as total')
            ->whereYear('tanggal_pembelian', $year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [str_pad($item->month, 2, '0', STR_PAD_LEFT) => $item->total];
            });
    
        return response()->json([
            'year' => $year,
            'total_harga_year' => $totalYear,
            'monthly_totals' => $monthlyTotals
        ], 200);
    }
    

}

