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
            'kode' => 'required|string|unique:kode_barangs,kode',
            'uraian' => 'required|string',
            'parent_id' => 'nullable|exists:kode_barangs,id',
        ]);

        $kodeBarang = KodeBarang::create($validated);
        return response()->json(['message' => 'Kode Barang created successfully', 'data' => $kodeBarang], 201);
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
            'kode' => 'string|unique:kode_barangs,kode,' . $id,
            'uraian' => 'string',
            'parent_id' => 'nullable|exists:kode_barangs,id',
        ]);

        $kodeBarang = KodeBarang::findOrFail($id);
        $kodeBarang->update($validated);

        return response()->json(['message' => 'Kode Barang updated successfully', 'data' => $kodeBarang]);
    }

    /**
     * Remove the specified resource from storage.
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
        $request->validate([
            'xlsx_file' => 'required|mimes:xlsx',
        ]);
    
        $file = $request->file('xlsx_file');
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();
    
        $lastParents = [];
    
        foreach ($rows as $index => $row) {
            if ($index === 0) continue;
    
            $kode = trim($row[0]);
            $uraian = trim($row[1]);
    
            if (!$kode || !$uraian) continue;
    
            $level = substr_count(rtrim($kode, '.'), '.');
    
            $parentId = $level > 0 ? ($lastParents[$level - 1] ?? null) : null;
    
            $item = KodeBarang::create([
                'kode' => $kode,
                'uraian' => $uraian,
                'parent_id' => $parentId,
            ]);
    
            $lastParents[$level] = $item->id;
        }
    
        return response()->json(['message' => 'XLSX imported successfully'], 200);
    }
    
}
