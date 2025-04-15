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
        Schema::create('mutasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bhp_atribut_id')->constrained('bhp_atribut')->onDelete('cascade');
            $table->foreignId('mutasi_penambahan_id')->constrained('mutasi_penambahan')->onDelete('cascade');
            $table->foreignId('mutasi_pengurangan_id')->constrained('mutasi_pengurangan')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi');
    }
};
