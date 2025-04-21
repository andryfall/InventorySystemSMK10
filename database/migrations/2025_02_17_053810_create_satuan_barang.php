<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('satuan_barang', function (Blueprint $table) {
            $table->id();
            $table->string('nama_satuan', 50)->unique();
            $table->timestamps();
        });

        // Insert default data
        DB::table('satuan_barang')->insert([
            ['nama_satuan' => 'Pcs'],
            ['nama_satuan' => 'Box'],
            ['nama_satuan' => 'Buah'],
            ['nama_satuan' => 'Lembar'],
            ['nama_satuan' => 'Unit'],
            ['nama_satuan' => 'Set'],
            ['nama_satuan' => 'Dus'],
            ['nama_satuan' => 'Botol'],
            ['nama_satuan' => 'Roll'],
            ['nama_satuan' => 'Kardus'],
            ['nama_satuan' => 'Pack'],
            ['nama_satuan' => 'Paket'],
            ['nama_satuan' => 'Karung'],
            ['nama_satuan' => 'Sak'],
            ['nama_satuan' => 'Drum'],
            ['nama_satuan' => 'Tabung'],
            ['nama_satuan' => 'Koli'],
            ['nama_satuan' => 'Biji'],
            ['nama_satuan' => 'Lusin'],
            ['nama_satuan' => 'Gelas'],
            ['nama_satuan' => 'Rim'],
            ['nama_satuan' => 'Stel'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('satuan_barang');
    }
};
