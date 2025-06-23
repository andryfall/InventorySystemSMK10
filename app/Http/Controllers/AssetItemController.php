<?php

namespace App\Http\Controllers;

use App\Models\AssetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Validator;
use App\Models\KodeBarang;


class AssetItemController extends Controller
{
    public function index()
    {
            $assets = AssetItem::with(['kodeBarang.parent.parent'])
            ->where('jumlah', '>', 0)
            ->get();
    
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
                    'lokasi' => $asset->lokasi?->nama_gedung ?? 'Unknown',
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
                    'sumber_perolehan' => $asset->sumber_perolehan,
                    'kondisi' => $asset->kondisi,
                    'created_at' => $asset->created_at,
                    'updated_at' => $asset->updated_at,
                ];
            })
        ]);
    }

public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls',
    ]);

    $file = $request->file('file');
    $spreadsheet = IOFactory::load($file->getPathname());
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    $header = array_map('trim', $rows[0]);
    unset($rows[0]);

    $imported = [];
    $errors = [];

    foreach ($rows as $index => $row) {
        $rowData = array_combine($header, $row);

        if (!array_filter($rowData)) {
            continue;
        }

        $validator = Validator::make($rowData, [
            'Kode Barang' => 'required|string|exists:kode_barang,kode',
            'Merk/Tipe' => 'required|string|max:250',
            'Satuan' => 'required|string|max:8',
            'Volume' => 'required|integer|min:1',
            'Harga Satuan' => 'required|numeric|min:1',
            'Nilai Perolehan' => 'nullable|numeric',
            'Umur Ekonomi' => 'nullable|integer',
            'Beban Penyusutan' => 'nullable|numeric',
            'Hari' => 'required|integer|min:1|max:31',
            'Bulan' => 'required|integer|min:1|max:12',
            'Tahun' => 'required|integer|min:1900',
        ]);

        if ($validator->fails()) {
            $errors[] = [
                'row' => $index + 2,
                'messages' => $validator->errors()->all()
            ];
            continue;
        }

        try {
            $kodeBarang = KodeBarang::where('kode', $rowData['Kode Barang'])->firstOrFail();

            $tanggalPembelian = sprintf(
                '%04d-%02d-%02d',
                (int)$rowData['Tahun'],
                (int)$rowData['Bulan'],
                (int)$rowData['Hari']
            );

            $assetData = [
                'kode_barang_id' => $kodeBarang->id,
                'merk_barang' => $rowData['Merk/Tipe'],
                'satuan' => $rowData['Satuan'],
                'jumlah' => (int)$rowData['Volume'],
                'harga' => (int)$rowData['Harga Satuan'],
                'tanggal_pembelian' => $tanggalPembelian,
                'nilai_perolehan' => isset($rowData['Nilai Perolehan']) ? floatval(str_replace(',', '', $rowData['Nilai Perolehan'])) : null,
                'umur_ekonomis' => $rowData['Umur Ekonomi'] ?? null,
                'beban_penyusutan' => isset($rowData['Beban Penyusutan']) ? floatval(str_replace(',', '', $rowData['Beban Penyusutan'])) : null,
                'kondisi' => 'Baik',
                'sumber_perolehan' => $rowData['Sumber Perolehan'] ?? null,
                'kode_rekening_belanja' => $rowData['Kodering Belanja'] ?? null,
                'no_spk_faktur_kuitansi' => $rowData['No. SPK/FAKTUR/KUITANSI'] ?? null,
                'no_bast' => $rowData['NO BA PENERIMAAN'] ?? null,
            ];

            $imported[] = AssetItem::create($assetData);
        } catch (\Throwable $e) {
            $errors[] = [
                'row' => $index + 2,
                'messages' => [$e->getMessage()]
            ];
        }
    }

    return response()->json([
        'message' => 'Import process completed',
        'imported_count' => count($imported),
        'errors' => $errors
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|exists:kode_barang,kode',
            'nama_gedung' => 'sometimes|exists:lokasi,nama_gedung',
            'merk_barang' => 'required|string|max:250',
            'satuan' => 'required|string|max:8',
            'jumlah' => 'required|integer|min:1',
            'harga' => 'required|integer|min:1',
            'tanggal_pembelian' => 'required|date',
            'no_spk_faktur_kuitansi' => 'sometimes|string|max:50',
            'kode_rekening_belanja' => 'sometimes|string|max:50',
            'no_bast' => 'sometimes|string|max:50',
            'umur_ekonomis' => 'sometimes|integer',
            'nilai_perolehan' => 'sometimes|integer',
            'beban_penyusutan' => 'sometimes|numeric',
            'sumber_perolehan' => 'sometimes|string|max:50',
            'kondisi' => 'required|string|max:50',
        ]);
    
        $kodeBarang = \App\Models\KodeBarang::where('kode', $validated['kode'])->firstOrFail();
        
        $validated['kode_barang_id'] = $kodeBarang->id;
    
        unset($validated['kode']);

        if (isset($validated['nama_gedung'])) {
            $lokasi = \App\Models\Lokasi::where('nama_gedung', $validated['nama_gedung'])->firstOrFail();
            $validated['lokasi_id'] = $lokasi->id;
            unset($validated['nama_gedung']);
        }
    
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
                'sumber_perolehan' => $asset->sumber_perolehan,
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
            'jumlah' => 'sometimes|integer|min:1',
            'harga' => 'sometimes|integer|min:1',
            'tanggal_pembelian' => 'sometimes|date',
            'no_spk_faktur_kuitansi' => 'sometimes|string|max:50',
            'kode_rekening_belanja' => 'sometimes|string|max:50',
            'no_bast' => 'sometimes|string|max:50',
            'umur_ekonomis' => 'sometimes|integer',
            'nilai_perolehan' => 'sometimes|integer',
            'beban_penyusutan' => 'sometimes|numeric',
            'sumber_perolehan' => 'sometimes|string|max:50',
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
            ->select(DB::raw('SUM(jumlah * harga) as total'))
            ->value('total');
    
        $totalYear = AssetItem::whereYear('tanggal_pembelian', now()->year)
            ->select(DB::raw('SUM(jumlah * harga) as total'))
            ->value('total');
    
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
    
    public function destroyAll()
    {
        AssetItem::truncate();

        return response()->json([
            'message' => 'All Asset records deleted successfully'
        ]);
    }


}

