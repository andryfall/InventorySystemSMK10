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
        Schema::table('mutasi', function (Blueprint $table) {
            $table->index('bhp_item_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi', function (Blueprint $table) {
            $table->dropIndex(['bhp_item_id']);
            $table->dropIndex(['created_at']);
            $table->dropIndex('idx_mutasi_item_date');
        });
    }
};
