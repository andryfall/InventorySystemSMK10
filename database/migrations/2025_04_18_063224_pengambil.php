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
        Schema::create('pengambil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjam_id')->constrained('peminjam')->onDelete('cascade');
            $table->foreignId('mutasi_pengurangan_id');
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
