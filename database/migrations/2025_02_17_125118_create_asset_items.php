<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asset_items', function (Blueprint $table) {
            $table->id('aset');
            $table->foreignId('kode barang')->constrained('kode_barang')->onDelete('cascade');
            $table->foreignId('lokasi')->constrained('lokasi')->onDelete('cascade');
            $table->string('keterangan', 250);
            $table->string('uraian', 250);
            $table->string('satuan', 8);
            $table->integer('jumlah');
            $table->integer('harga');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
