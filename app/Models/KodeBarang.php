<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KodeBarang extends Model
{
    use HasFactory;

    protected $table = 'kode_barang'; // Ensure Laravel uses the correct table name

    protected $fillable = [
        'kode',
        'uraian',
        'parent_id',
    ];

    /**
     * Get the children of this KodeBarang (subcategories or sub-items).
     */
    public function children()
    {
        return $this->hasMany(KodeBarang::class, 'parent_id');
    }

    /**
     * Get the parent of this KodeBarang (if it belongs to a higher-level category).
     */
    public function parent()
    {
        return $this->belongsTo(KodeBarang::class, 'parent_id');
    }
}
