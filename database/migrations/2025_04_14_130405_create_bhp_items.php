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
            $table->id();
            $table->string('nama_barang');
            $table->foreignId('kode_rekening_id')->constrained('kode_rekening')->onDelete('cascade');
            $table->string('merk');
            $table->integer('harga');
            $table->string('satuan');
            $table->integer('total_volume')->default(0);
            $table->integer('initial_volume')->default(0);
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