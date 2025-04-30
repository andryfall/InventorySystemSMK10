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
        Schema::create('bhp_atribut', function (Blueprint $table) {
            $table->id();
            $table->string('pemegang_barang', 250);
            $table->string('merk_barang', 250);

            // Not a primary key, just an auto-incrementing number if needed
            $table->integer('kode_rekening_belanja');
            $table->string('nama_barang', 250);

            $table->integer('volume'); // or bigInteger if needed
            $table->integer('harga');
            $table->string('uraian', 250);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bhp_atribut');
    }
};
