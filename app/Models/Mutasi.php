<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mutasi extends Model
{
    use HasFactory;

    protected $table = 'mutasi';

    protected $fillable = [
        'bhp_item_id',
        'type',
        'quantity',
        'taker_name',
        'created_at',
    ];

    public function bhpItem()
    {
        return $this->belongsTo(BhpItem::class);
    }
}
