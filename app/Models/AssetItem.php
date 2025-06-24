<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'aset';
    public $incrementing = true;

    protected $fillable = [
        'kode_barang_id',
        'lokasi_id',
        'nama_barang',
        'merk_barang',
        'satuan',
        'jumlah',
        'harga',
        'tanggal_pembelian',
        'no_spk_faktur_kuitansi',
        'kode_rekening_belanja',
        'no_bast',
        'kode_rekening_aset',
        'nama_rekening_aset',
        'umur_ekonomis',
        'nilai_perolehan',
        'beban_penyusutan',
        'kondisi',
        'sumber_perolehan'
    ];

    public function kodeBarang()
    {
        return $this->belongsTo(KodeBarang::class);
    }

    public function peminjaman()
    {
        return $this->hasMany(PeminjamanAset::class);
    }

    public function lokasi()
    {
        return $this->belongsTo(Lokasi::class);
    }
}
