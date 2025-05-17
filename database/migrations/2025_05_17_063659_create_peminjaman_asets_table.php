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
            // Migration for peminjaman_asets
            Schema::create('peminjaman_asets', function (Blueprint $table) {
                $table->id();
                
                $table->unsignedBigInteger('asset_item_id');

                $table->foreign('asset_item_id')->references('aset')->on('asset_items')->onDelete('cascade');

                $table->string('nama_peminjam');
                $table->integer('volume');
                $table->text('keperluan');
                $table->string('status')->default('dipinjam');
                $table->date('tanggal_pinjam')->default(now());
                $table->date('tanggal_kembali')->nullable();

                $table->timestamps();
            });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman_asets');
    }
};
