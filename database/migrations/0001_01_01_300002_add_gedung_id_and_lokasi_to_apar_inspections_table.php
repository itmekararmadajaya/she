<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::table('apar_inspections', function (Blueprint $table) {
            // Menambahkan kolom foreign key ke tabel `gedungs`
            $table->foreignId('gedung_id')->nullable()->constrained()->onDelete('set null')->after('master_apar_id');
            // Menambahkan kolom string untuk lokasi spesifik
            $table->string('lokasi')->after('gedung_id');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::table('apar_inspections', function (Blueprint $table) {
            // Hapus foreign key terlebih dahulu
            $table->dropForeign(['gedung_id']);
            // Hapus kolom
            $table->dropColumn('gedung_id');
            $table->dropColumn('lokasi');
        });
    }
};
