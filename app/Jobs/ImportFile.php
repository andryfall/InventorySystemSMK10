<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;

use App\Models\KodeBarang;

class ImportFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    public function handle()
    {
        $spreadsheet = IOFactory::load($this->filePath);
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
    
            $existingItem = KodeBarang::where('kode', $kode)->first();
    
            if ($existingItem) {
                Log::warning("Skipped duplicate entry: $kode - $uraian");
                continue; // Skip if already exists
            }
    
            $item = KodeBarang::create([
                'kode' => $kode,
                'uraian' => $uraian,
                'parent_id' => $parentId,
            ]);
    
            $lastParents[$level] = $item->id;
        }
    }
    
}
