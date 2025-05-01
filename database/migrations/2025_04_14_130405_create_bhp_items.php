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
        Schema::create('bhp_items', function (Blueprint $table) {
<<<<<<<< HEAD:database/migrations/2025_04_17_130405_create_bhp_items.php
            $table->id('bhp');
            $table->foreignId('kode_barang_id')->constrained('kode_barang')->onDelete('cascade');
            $table->foreignID('bhp_atribut_id')->constrained('bhp_atribut')->onDelete('cascade');
            $table->foreignID('satuan_barang_id')->constrained('satuan_barang')->onDelete('cascade');
            $table->foreignId('saldo_awal_id')->constrained('bhp_saldo_awal')->onDelete('cascade');
            $table->foreignId('peminjam_id')->constrained('peminjam')->onDelete('cascade');
            $table->foreignId('mutasi_id')->constrained('mutasi')->onDelete('cascade');
========
            $table->id();
            $table->string('nama_barang');
            $table->foreignId('kode_rekening_id')->constrained('kode_rekening')->onDelete('cascade');
            $table->string('merk');
            $table->integer('harga');
            $table->string('satuan');
            $table->integer('total_volume')->default(0);
            $table->integer('initial_volume')->default(0);
>>>>>>>> main:database/migrations/2025_04_14_130405_create_bhp_items.php
            $table->timestamps();
        });
        
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bhp_items');
    }
};
