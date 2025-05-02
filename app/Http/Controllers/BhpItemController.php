<?php

namespace App\Http\Controllers;

use App\Models\BhpItem;
use App\Models\Mutasi;
use App\Models\KodeRekening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;

class BhpItemController extends Controller
{

    public function index()
    {
        $startOfCurrentMonth = Carbon::now()->startOfMonth();
    
        $items = BhpItem::with('kodeRekening')
            ->get()
            ->map(function ($item) use ($startOfCurrentMonth) {
                $stockAwal = $item->mutasi()
                    ->where('created_at', '<', $startOfCurrentMonth)
                    ->select(DB::raw("COALESCE(SUM(CASE WHEN type = 'add' THEN quantity ELSE -quantity END), 0) as total"))
                    ->value('total');
    
                $stockAkhir = $item->mutasi()
                    ->select(DB::raw("COALESCE(SUM(CASE WHEN type = 'add' THEN quantity ELSE -quantity END), 0) as total"))
                    ->value('total');
    
                return [
                    'id'            => $item->id,
                    'Nama Barang'   => $item->nama_barang,
                    'Kode Rekening' => $item->kodeRekening->kode ?? '-',
                    'Merk'          => $item->merk,
                    'Tanggal'       => $item->updated_at->toDateString(),
                    'Stock Awal'    => $stockAwal,
                    'Stock Akhir'   => $stockAkhir,
                    'Harga Satuan'  => $item->harga,
                    'Jumlah Awal'   => $stockAwal * $item->harga,
                    'Jumlah Akhir'  => $stockAkhir * $item->harga,
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
        ]);

        $kodeRekening = KodeRekening::where('kode', $validated['kode_rekening'])->firstOrFail();

        $item = BhpItem::create([
            'nama_barang'     => $validated['nama_barang'],
            'kode_rekening_id'=> $kodeRekening->id,
            'merk'            => $validated['merk'],
            'volume'          => $validated['volume'],
            'satuan'          => $validated['satuan'],
            'harga'           => $validated['harga'],
        ]);

        Mutasi::create([
            'bhp_item_id' => $item->id,
            'quantity'    => $validated['volume'],
            'type'        => 'add',
            'created_at'  => now(),
        ]);

        return response()->json([
            'message' => 'BHP item added successfully.',
            'item'    => $item
        ], 201);
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
            foreach ($rows as $row) {
                [$namaBarang, $kodeRekeningStr, $merk, $volume, $satuan, $harga] = $row;
    
                $kodeRekening = KodeRekening::where('kode', $kodeRekeningStr)->first();
                if (!$kodeRekening) {
                    throw new \Exception("Kode rekening not found: $kodeRekeningStr");
                }
    
                $item = BhpItem::where('nama_barang', $namaBarang)
                    ->where('kode_rekening_id', $kodeRekening->id)
                    ->where('merk', $merk)
                    ->where('satuan', $satuan)
                    ->where('harga', $harga)
                    ->first();
    
                if ($item) {
                    $item->total_volume += (int) $volume;
                    $item->save();
                } else {
                    $item = BhpItem::create([
                        'nama_barang' => $namaBarang,
                        'kode_rekening_id' => $kodeRekening->id,
                        'merk' => $merk,
                        'satuan' => $satuan,
                        'harga' => $harga,
                        'total_volume' => (int) $volume,
                    ]);
                }
    
                Mutasi::create([
                    'bhp_item_id' => $item->id,
                    'quantity' => (int) $volume,
                    'type' => 'add',
                    'created_at' => now(),
                ]);
            }
    
            DB::commit();
            return response()->json(['message' => 'Items imported successfully.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Import failed', 'error' => $e->getMessage()], 500);
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
                ];
            });

        return response()->json($removals);
    }


}
