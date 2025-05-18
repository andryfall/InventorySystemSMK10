<?php

namespace App\Http\Controllers;

use App\Models\KodeRekening;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class KodeRekeningController extends Controller
{
    public function index()
    {
        $data = KodeRekening::orderBy('id', 'asc')->get();
        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:kode_rekening,kode',
            'uraian' => 'required|string|max:250',
        ]);

        $kodeRekening = KodeRekening::create($validated);

        return response()->json($kodeRekening, 201);
    }

    public function show($id)
    {
        $item = KodeRekening::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $item = KodeRekening::findOrFail($id);

        $validated = $request->validate([
            'kode' => 'string|max:50|unique:kode_rekening,kode,' . $id,
            'uraian' => 'string|max:250',
        ]);

        $item->update($validated);

        return response()->json($item);
    }

    public function destroy($id)
    {
        $item = KodeRekening::findOrFail($id);
        $item->delete();

        return response()->json(null, 204);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls'
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        unset($rows[0]);

        foreach ($rows as $row) {
            if (!empty($row[0]) && !empty($row[1])) {
                KodeRekening::updateOrCreate(
                    ['kode' => $row[0]], 
                    ['uraian' => $row[1]]
                );
            }
        }

        return response()->json(['message' => 'Import successful']);
    }

    public function destroyAll()
    {
        KodeRekening::truncate();

        return response()->json([
            'message' => 'All Kode Rekening records deleted successfully'
        ]);
    }

}
