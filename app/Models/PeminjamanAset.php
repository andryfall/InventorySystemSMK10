<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PeminjamanAset extends Model
{
    use HasFactory;

    protected $table = 'peminjaman_asets';

    protected $fillable = [
        'asset_item_id',
        'nama_peminjam',
        'volume',
        'keperluan',
        'tanggal_pinjam',
        'tanggal_kembali',
        'status',
    ];

    protected $dates = [
        'tanggal_pinjam',
        'tanggal_kembali',
    ];

    public function assetItem()
    {
        return $this->belongsTo(AssetItem::class);
    }
}
