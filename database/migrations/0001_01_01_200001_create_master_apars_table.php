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
        Schema::create('master_apars', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 4)->unique();
            $table->foreignId('jenis_pemadam_id')->nullable()->constrained('jenis_pemadams')->onDelete('set null');
            $table->foreignId('jenis_isi_id')->nullable()->constrained('jenis_isis')->onDelete('set null');
            $table->integer('ukuran');
            $table->string('satuan', 8);
            $table->foreignId('gedung_id')->constrained('gedungs')->onDelete('cascade');
            $table->string('lokasi');
            $table->date('tgl_kadaluarsa');
            $table->string('tanda');
            $table->string('catatan')->nullable();
            $table->date('tgl_refill');
            $table->string('keterangan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_apars');
    }
};
