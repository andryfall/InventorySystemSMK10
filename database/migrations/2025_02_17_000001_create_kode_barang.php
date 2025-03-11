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
        Schema::create('kode_barang', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 50)->unique();
            $table->string('uraian', 250);
            $table->integer('umur_ekonomis');
            $table->unsignedBigInteger(column: 'parent_id')->nullable();
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('kode_barang')->onDelete('cascade');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kode_barang');
    }
};
