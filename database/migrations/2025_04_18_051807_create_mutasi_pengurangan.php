<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create the view for mutasi_pengurangan
        DB::statement("
            CREATE VIEW mutasi_pengurangan AS
            SELECT
                bhp_atribut.id AS id,
                bhp_atribut.id AS bhp_atribut_id,
                bhp_atribut.volume,
                bhp_atribut.harga,
                (bhp_atribut.volume * bhp_atribut.harga) AS jumlah_pengurangan
            FROM bhp_atribut
            JOIN bhp_items ON bhp_items.bhp_atribut_id = bhp_atribut.id
            WHERE bhp_items.mutasi_id IS NOT NULL
        ");
        
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS mutasi_pengurangan");
    }
};
