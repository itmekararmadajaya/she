<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengubah kolom menjadi nullable.
     */
    public function up(): void
    {
        Schema::table('master_apars', function (Blueprint $table) {
            // Menggunakan change() untuk mengubah definisi kolom
            $table->string('tanda')->nullable()->change();
            $table->string('keterangan')->nullable()->change();
            $table->date('tgl_refill')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     * Mengembalikan kolom ke kondisi semula (tidak nullable).
     */
    public function down(): void
    {
        Schema::table('master_apars', function (Blueprint $table) {
            // Mengembalikan kolom ke non-nullable (jika diperlukan)
            $table->string('tanda')->change();
            $table->string('keterangan')->change();
            $table->date('tgl_refill')->change();
        });
    }
};
