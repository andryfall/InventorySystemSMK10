<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BhpItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_barang',
        'kode_rekening_id',
        'merk',
        'harga',
        'satuan',
        'total_volume',
        'initial_volume',
    ];

    public function kodeRekening()
    {
        return $this->belongsTo(KodeRekening::class);
    }

    public function mutasi()
    {
        return $this->hasMany(Mutasi::class);
    }

}
