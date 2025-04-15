<?php

use GuzzleHttp\Promise\Create;
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
        Schema::create('mutasi_penambahan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bhp_atribut_id')->constrained('bhp_atribut')->onDelete('cascade');
            $table->timestamps();
        });
        DB::statement("Create View mutasi_penambahan as(
        select bhp_atribut_id, bhp_atribut.volume, bhp_atribut.harga),
        (bhp_atribut.volume * bhp_atribut.harga) as jumlah penambahan,
        from bhp_atribut
        join bhp_items on bhp_items.bhp_atribut_id = bhp_atribut.id");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mutasi_penambahan');
    }
};
