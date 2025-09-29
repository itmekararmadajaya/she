<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_apar_id')->nullable()->constrained('master_apars')->onDelete('set null');
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('kebutuhan_id')->constrained('kebutuhans')->onDelete('cascade');
            $table->foreignId('biaya_id')->constrained('harga_kebutuhans')->onDelete('cascade');
            $table->date('tanggal_pembelian');
            $table->date('tanggal_pelunasan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
