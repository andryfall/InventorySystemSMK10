<?php

namespace App\Http\Controllers;

use App\Models\BhpItem;
use App\Models\Mutasi;
use App\Models\KodeRekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;


class BhpItemController extends Controller
{

public function index()
{
    $startOfCurrentMonth = Carbon::now()->startOfMonth();

    $stockAwalMap = DB::table('mutasi')
        ->select('bhp_item_id', DB::raw("SUM(CASE WHEN type = 'add' THEN quantity ELSE -quantity END) as total"))
        ->where('created_at', '<', $startOfCurrentMonth)
        ->groupBy('bhp_item_id')
        ->pluck('total', 'bhp_item_id');

    $items = BhpItem::with('kodeRekening')
        ->get()
        ->map(function ($item) use ($stockAwalMap) {
            $stockAwal = $stockAwalMap[$item->id] ?? 0;

            return [
                'id'            => $item->id,
                'Nama Barang'   => $item->nama_barang,
                'Kode Rekening' => $item->kodeRekening->kode ?? '-',
                'Merk'          => $item->merk,
                'Tanggal'       => $item->updated_at->toDateString(),
                'Stock Awal'    => $stockAwal,
                'Stock Akhir'   => $item->total_volume,
                'Harga Satuan'  => $item->harga,
                'Jumlah Awal'   => $stockAwal * $item->harga,
                'Jumlah Akhir'  => $item->total_volume * $item->harga,
            ];
        })
        ->sortBy('id')
        ->values();

    return response()->json($items);
}


    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_barang'     => 'required|string|max:255',
            'kode_rekening'   => 'required|exists:kode_rekening,kode',
            'merk'            => 'required|string|max:255',
            'volume'          => 'required|integer|min:1',
            'satuan'          => 'required|string|max:100',
            'harga'           => 'required|integer|min:0',
            'tanggal'         => 'nullable|string',
        ]);
        
        $tanggal = now();

        if (!empty($validated['tanggal'])) {
            try {
                $tanggal = Carbon::createFromFormat('m/d/Y', $validated['tanggal'])->startOfDay();
            } catch (\Exception $e) {
                return response()->json(['message' => 'Invalid tanggal format. Use m/d/Y'], 422);
            }
        }

        $kodeRekening = KodeRekening::where('kode', $validated['kode_rekening'])->firstOrFail();

        DB::beginTransaction();
        try {
            $item = BhpItem::where('nama_barang', $validated['nama_barang'])
                ->where('kode_rekening_id', $kodeRekening->id)
                ->where('merk', $validated['merk'])
                ->where('satuan', $validated['satuan'])
                ->where('harga', $validated['harga'])
                ->first();

            if ($item) {
                $item->total_volume += $validated['volume'];
                $item->save();
            } else {
                $item = BhpItem::create([
                    'nama_barang'     => $validated['nama_barang'],
                    'kode_rekening_id'=> $kodeRekening->id,
                    'merk'            => $validated['merk'],
                    'satuan'          => $validated['satuan'],
                    'harga'           => $validated['harga'],
                    'total_volume'    => $validated['volume'],
                ]);
            }

            Mutasi::create([
                'bhp_item_id' => $item->id,
                'quantity'    => $validated['volume'],
                'type'        => 'add',
                'created_at'  => $tanggal,
            ]);

            DB::commit();
            return response()->json([
                'message' => 'BHP item added successfully.',
                'item'    => $item
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Add item failed', 'error' => $e->getMessage()], 500);
        }
    }

public function import(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,xls',
    ]);

    $file = $request->file('file');
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    unset($rows[0]);

    DB::beginTransaction();
    try {
        foreach ($rows as $index => $row) {
            if (count(array_filter($row)) < 6) continue;

            $tanggal = now();
            if (isset($row[6]) && $row[6]) {
                
                try {
                    $tanggal = Carbon::createFromFormat('m/d/Y', trim($row[6]))->startOfDay();
                } catch (\Exception $e) {
                    throw new \Exception("Invalid date format in row " . ($index + 2) . ": " . $row[6]);
                }
            }

            [$namaBarang, $kodeRekeningStr, $merk, $volume, $satuan, $harga] = array_slice($row, 0, 6);

            $kodeRekening = KodeRekening::where('kode', trim($kodeRekeningStr))->first();
            if (!$kodeRekening) {
                throw new \Exception("Kode rekening not found in row " . ($index + 2) . ": " . $kodeRekeningStr);
            }

            $item = BhpItem::where('nama_barang', trim($namaBarang))
                ->where('kode_rekening_id', $kodeRekening->id)
                ->where('merk', trim($merk))
                ->where('satuan', trim($satuan))
                ->where('harga', (int) $harga)
                ->first();

            if ($item) {
                $item->total_volume += (int) $volume;
                $item->save();
            } else {
                $item = BhpItem::create([
                    'nama_barang'      => trim($namaBarang),
                    'kode_rekening_id' => $kodeRekening->id,
                    'merk'             => trim($merk),
                    'satuan'           => trim($satuan),
                    'harga'            => (int) $harga,
                    'total_volume'     => (int) $volume,
                ]);
            }

            Mutasi::create([
                'bhp_item_id' => $item->id,
                'quantity'    => (int) $volume,
                'type'        => 'add',
                'created_at'  => $tanggal,
            ]);
        }

        DB::commit();
        return response()->json(['message' => 'Items imported successfully.']);
    } catch (\Throwable $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Import failed',
            'error'   => $e->getMessage()
        ], 500);
    }
}


    public function remove(Request $request, $id)
    {
        $validated = $request->validate([
            'volume' => 'required|integer|min:1',
            'taker_name' => 'required|string',
        ]);
    
        $item = BhpItem::findOrFail($id);
    
        if ($item->total_volume < $validated['volume']) {
            return response()->json(['message' => 'Not enough stock to remove.'], 400);
        }
    
        $item->total_volume -= $validated['volume'];
        $item->save();
    
        Mutasi::create([
            'bhp_item_id' => $item->id,
            'quantity' => $validated['volume'],
            'type' => 'remove',
            'taker_name' => $validated['taker_name'],
            'created_at' => now(),
        ]);
    
        return response()->json(['message' => 'BHP item removed successfully.']);
    }    

    public function undoRemoval($id)
    {
        $mutasi = Mutasi::where('id', $id)->where('type', 'remove')->first();

        if (!$mutasi) {
            return response()->json(['message' => 'Removal record not found.'], 404);
        }

        $item = BhpItem::find($mutasi->bhp_item_id);

        if (!$item) {
            return response()->json(['message' => 'BHP item not found.'], 404);
        }

        $item->total_volume += $mutasi->quantity;
        $item->save();

        $mutasi->delete();

        return response()->json(['message' => 'Removal undone and item stock restored.']);
    }

    public function getRemovalLogs()
    {
        $removals = Mutasi::where('type', 'remove')
            ->with(['bhpItem.koderekening'])
            ->latest()
            ->get()
            ->map(function ($log) {
                return [
                    'Id'              => $log->id,
                    'Nama Barang'     => $log->bhpItem->nama_barang,
                    'Kode Rekening'   => $log->bhpItem->koderekening->kode ?? '-',
                    'Merk'            => $log->bhpItem->merk,
                    'Peminjam'        => $log->taker_name,
                    'Jumlah Barang'   => $log->quantity,
                    'Total'           => $log->quantity * $log->bhpItem->harga,
                    'tanggal'         => $log->created_at,
                ];
            });

        return response()->json($removals);
    }

    public function getMutasiLogs()
    {
        $logs = Mutasi::where('type', 'add')
            ->with(['bhpItem.koderekening'])
            ->latest()
            ->get()
            ->map(function ($log) {
                return [
                    'Id'              => $log->id,
                    'Nama Barang'     => $log->bhpItem->nama_barang,
                    'Kode Rekening'   => $log->bhpItem->koderekening->kode ?? '-',
                    'Merk'            => $log->bhpItem->merk,
                    'Peminjam'        => $log->taker_name,
                    'Jumlah Barang'   => $log->quantity,
                    'Total'           => $log->quantity * $log->bhpItem->harga,
                    'tanggal'         => $log->created_at,
                ];
            });

        return response()->json($logs);
    }


    public function totalJumlahAkhirByYear($year)
    {
        $totalYear = BhpItem::selectRaw("
            SUM(
                (
                    SELECT COALESCE(SUM(CASE WHEN type = 'add' THEN quantity ELSE -quantity END), 0)
                    FROM mutasi
                    WHERE mutasi.bhp_item_id = bhp_items.id
                      AND DATE_PART('year', mutasi.created_at) <= ?
                ) * harga
            ) AS total
        ", [$year])->value('total');
    
        $rawMonthly = DB::table('mutasi')
            ->join('bhp_items', 'mutasi.bhp_item_id', '=', 'bhp_items.id')
            ->selectRaw("
                DATE_PART('month', mutasi.created_at) as month,
                SUM(
                    (CASE WHEN mutasi.type = 'add' THEN mutasi.quantity ELSE -mutasi.quantity END) * bhp_items.harga
                ) as total
            ")
            ->whereRaw("DATE_PART('year', mutasi.created_at) = ?", [$year])
            ->groupByRaw("DATE_PART('month', mutasi.created_at)")
            ->orderByRaw("DATE_PART('month', mutasi.created_at)")
            ->get()
            ->mapWithKeys(function ($item) {
                return [str_pad($item->month, 2, '0', STR_PAD_LEFT) => $item->total];
            });
    
        $monthlyTotals = [];
        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad($m, 2, '0', STR_PAD_LEFT);
            $monthlyTotals[$key] = $rawMonthly[$key] ?? 0;
        }
    
        return response()->json([
            'year' => $year,
            'total_jumlah_akhir_year' => $totalYear ?? 0,
            'monthly_totals' => $monthlyTotals
        ], 200);
    }
    
    public function totalUniquePeminjam()
    {
        $total = Mutasi::where('type', 'remove')
            ->distinct('taker_name')
            ->count('taker_name');

        return response()->json(['total_unique_peminjam' => $total]);
    }

    public function totalStockAkhir()
    {
        $items = BhpItem::withSum(['mutasi as stock_akhir' => function ($query) {
            $query->select(DB::raw("COALESCE(SUM(CASE WHEN type = 'add' THEN quantity ELSE -quantity END), 0)"));
        }], 'quantity')->get();

        $totalStock = $items->sum('stock_akhir');

        return response()->json(['total_stock_akhir' => $totalStock]);
    }

public function exportBhpItems(Request $request)
{
    $request->validate([
        'month' => 'required|integer|min:1|max:12',
        'year' => 'required|integer|min:1900',
    ]);

    $month = $request->input('month');
    $year = $request->input('year');

    $startOfMonth = now()->setDate($year, $month, 1)->startOfMonth();
    $endOfMonth = $startOfMonth->copy()->endOfMonth();

    $items = BhpItem::with('kodeRekening')->get();

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->fromArray([
        'No',
        'Nama Barang',
        'Kode Rekening',
        'Merk',
        'Tanggal',
        'Stock Awal',
        'Satuan',
        'Stock Akhir',
        'Harga Satuan',
        'Jumlah Awal',
        'Jumlah Akhir'
    ], null, 'A1');

    $rowNum = 2;
    $counter = 1;

    foreach ($items as $item) {
        $stockAwal = $item->mutasi()
            ->where('created_at', '<', $startOfMonth)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'add' THEN quantity ELSE -quantity END), 0) AS total")
            ->value('total') ?? 0;

        $stockAkhir = $item->mutasi()
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'add' THEN quantity ELSE -quantity END), 0) AS total")
            ->value('total') ?? 0;

        $sheet->fromArray([
            $counter++,
            $item->nama_barang,
            $item->kodeRekening->kode ?? '',
            $item->merk,
            $endOfMonth->format('Y-m-d'),
            $stockAwal,
            $item->satuan,
            $stockAkhir,
            $item->harga,
            $stockAwal * $item->harga,
            ($stockAwal + $stockAkhir) * $item->harga,
        ], null, 'A' . $rowNum++);
    }

    $writer = new Xlsx($spreadsheet);

    $filename = "BHP_Items_Export_{$year}_{$month}.xlsx";

    return new StreamedResponse(function () use ($writer) {
        $writer->save('php://output');
    }, 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ]);
}


    public function destroy($id)
    {
        $item = BhpItem::findOrFail($id);

        $hasRemovals = Mutasi::where('bhp_item_id', $id)
            ->where('type', 'remove')
            ->exists();

        if ($hasRemovals) {
            return response()->json([
                'message' => 'Cannot delete item that has already been removed before.'
            ], 400);
        }

        Mutasi::where('bhp_item_id', $id)->delete();

        $item->delete();

        return response()->json([
            'message' => 'BHP item deleted successfully.'
        ]);
    }

    public function destroyAll()
    {
        BhpItem::truncate();

        return response()->json([
            'message' => 'All BHP records deleted successfully'
        ]);
    }

    
    public function destroyAllRiwayat()
    {
        Mutasi::truncate();

        return response()->json([
            'message' => 'All Riwayat records deleted successfully'
        ]);
    }

    
}
