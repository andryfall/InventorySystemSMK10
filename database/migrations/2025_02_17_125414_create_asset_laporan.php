<?php

use GuzzleHttp\Promise\Create;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use SebastianBergmann\CodeCoverage\Report\Xml\BuildInformation;
use function Laravel\Prompts\select;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asset_laporan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aset')->constrained('asset_items')->onDelete('cascade');
            $table->foreignId('kode_barang_id')->constrained('kode_barang')->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_laporan');
    }
};
