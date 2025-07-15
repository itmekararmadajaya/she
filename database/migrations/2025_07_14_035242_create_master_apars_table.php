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
        Schema::create('master_apars', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 4)->unique();
            $table->string('jenis_pemadam');
            $table->string('jenis_isi');
            $table->integer('ukuran');
            $table->string('satuan', 2);
            $table->foreignId('gedung_id')->constrained()->onDelete('cascade');
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
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_apars');
    }
};
