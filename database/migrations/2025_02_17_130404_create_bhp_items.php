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
            $table->id('bhp');
            $table->foreignId('kode_barang_id')->constrained('kode_barang')->onDelete('cascade');
            $table->foreignID('bhp_atribut_id')->constrained('bhp_atribut')->onDelete('cascade');
            $table->foreignID('satuan_barang_id')->constrained('satuan_barang')->onDelete('cascade');
            $table->foreignId('saldo_awal_id')->constrained('bhp_saldo_awal')->onDelete('cascade');
            $table->foreignId('pengambil_id')->constrained('pengambil')->onDelete('cascade');
            $table->foreignId('mutasi_id')->constrained('mutasi')->onDelete('cascade');
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
