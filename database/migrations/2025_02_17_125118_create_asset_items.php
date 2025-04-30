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
        Schema::create('asset_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kode_barang_id')->constrained('kode_barang')->onDelete('cascade');
            $table->foreignId('lokasi_id')->constrained('lokasi')->onDelete('cascade');
            $table->string('merk_barang', 250);
            $table->string('satuan', 8);
            $table->integer('jumlah');
            $table->integer('harga');
            $table->date('tanggal_pembelian');
            $table->string('no_spk_faktur_kuitansi', 50);
            $table->string('kode_rekening_belanja', 50);
            $table->string('no_bast', 50);
            $table->integer('umur_ekonomis');
            $table->string('status', 8);
            $table->integer('nilai_perolehan');
            $table->integer('beban_penyusutan');
            $table->timestamps();
        });

        // Add CHECK constraint to status column
       DB::statement("ALTER TABLE asset_items ADD CONSTRAINT status_check CHECK (status IN ('Baik', 'Rusak', 'Berat'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
