<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\KodeBarang;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KodeBarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(KodeBarang::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:kode_barang,kode',
            'uraian' => 'required|string',
        ]);
    
        $parentKode = $this->getParentKode($validated['kode']);
        $parent = KodeBarang::where('kode', $parentKode)->first();
        $validated['parent_id'] = $parent ? $parent->id : null;
    
        $kodeBarang = KodeBarang::create($validated);
    
        return response()->json([
            'message' => 'Kode Barang created successfully',
            'data' => $kodeBarang
        ], 201);
    }
    
    /**
     * Extract the parent kode by removing the last segment
     */
    private function getParentKode($kode)
    {
        $parts = explode('.', rtrim($kode, '.'));
    
        if (count($parts) <= 1) {
            return null;
        }
    
        array_pop($parts);
        return implode('.', $parts) . '.';
    }
    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $kodeBarang = KodeBarang::findOrFail($id);
        return response()->json($kodeBarang);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'kode' => 'string|unique:kode_barang,kode,' . $id,
            'uraian' => 'string',
            'parent_id' => 'nullable|exists:kode_barang,id',
        ]);

        $kodeBarang = KodeBarang::findOrFail($id);
        $kodeBarang->update($validated);

        return response()->json(['message' => 'Kode Barang updated successfully', 'data' => $kodeBarang]);
    }

    /**
     */
    public function destroy($id)
    {
        KodeBarang::destroy($id);
        return response()->json(['message' => 'Kode Barang deleted successfully']);
    }

    /**
     * Import data from excel file.
     */
    public function importFile(Request $request)
    {
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '512M');
    
        $request->validate([
            'xlsx_file' => 'required|mimes:xlsx',
        ]);
    
        $file = $request->file('xlsx_file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
    
        $lastParents = [];
        $dataToInsert = [];
        $skippedItems = [];
    
        foreach ($rows as $index => $row) {
            if ($index === 0) continue;
    
            $kode = trim($row[0]);
            $uraian = trim($row[1]);
    
            if (!$kode || !$uraian) continue;
    
            // Determine level by counting periods (.)
            $level = substr_count(rtrim($kode, '.'), '.');
    
            // Find the correct parent ID from the last known parent at the previous level
            $parentId = $level > 0 && isset($lastParents[$level - 1]) ? $lastParents[$level - 1] : null;
    
            if (KodeBarang::where('kode', $kode)->exists()) {
                $skippedItems[] = [
                    'kode' => $kode,
                    'uraian' => $uraian,
                    'reason' => 'Duplicate entry',
                ];
                continue;
            }
    
            $item = KodeBarang::create([
                'kode' => $kode,
                'uraian' => $uraian,
                'parent_id' => $parentId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
    
            // Store the last inserted ID at the current level
            $lastParents[$level] = $item->id;
        }
    
        return response()->json([
            'message' => 'File imported successfully',
            'skipped_items' => $skippedItems
        ], 200);
    }
    
    public function totalKodeBarang()
    {
        $total = KodeBarang::count();
        return response()->json(['total_kodebarang' => $total], 200);
    }
    
}
