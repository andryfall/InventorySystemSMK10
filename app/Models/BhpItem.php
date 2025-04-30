<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BhpItem extends Model
{
    use HasFactory;

    protected $table = 'bhp_items';

    protected $primaryKey = 'bhp';

    protected $fillable = [
        'kode_barang_id',
        'bhp_atribut_id',
        'satuan_barang_id',
        'saldo_awal_id',
        'peminjam_id',
        'mutasi_id',
    ];

    public function kodeBarang()
    {
        return $this->belongsTo(KodeBarang::class);
    }

    public function bhpAtribut()
    {
        return $this->belongsTo(BhpAtribut::class);
    }

    public function satuanBarang()
    {
        return $this->belongsTo(SatuanBarang::class);
    }

    public function saldoAwal()
    {
        return $this->belongsTo(BhpSaldoAwal::class);
    }

    public function peminjam()
    {
        return $this->belongsTo(Peminjam::class);
    }

    public function mutasi()
    {
        return $this->belongsTo(Mutasi::class);
    }
}
