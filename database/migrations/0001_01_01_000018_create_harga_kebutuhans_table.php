<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_harga_kebutuhans_table.php
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
        Schema::create('harga_kebutuhans', function (Blueprint $table) {
            $table->id();
            
            // Kolom utama
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->foreignId('kebutuhan_id')->constrained('kebutuhans')->onDelete('cascade');

            // Kolom baru yang nullable, diurutkan sesuai permintaan
            $table->foreignId('jenis_pemadam_id')->nullable()->constrained('jenis_pemadams')->onDelete('cascade');
            $table->foreignId('jenis_isi_id')->nullable()->constrained('jenis_isis')->onDelete('cascade');
            $table->foreignId('item_check_id')->nullable()->constrained('item_checks')->onDelete('cascade');

            // Kolom lainnya
            $table->decimal('biaya', 15, 2);
            $table->date('tanggal_perubahan');
            $table->timestamps();
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_kebutuhans');
    }
};
