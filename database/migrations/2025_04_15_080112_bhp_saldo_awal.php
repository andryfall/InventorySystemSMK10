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
        //
        Schema::create('bhp_saldo_awal', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bhp_atribut_id')->constrained('bhp_atribut')->onDelete('cascade');
            $table->foreignId('satuan_barang_id')->constrained('satuan_barang')->onDelete('cascade');
            $table->integer('tanggal')->default(0);
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
