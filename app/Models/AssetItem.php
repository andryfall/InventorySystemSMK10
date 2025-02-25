<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang_id',
        'lokasi_id',
        'keterangan',
        'uraian',
        'satuan',
        'jumlah',
        'harga',
    ];

    public function kodeBarang()
    {
        return $this->belongsTo(KodeBarang::class);
    }

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }
}
