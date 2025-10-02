// 2025_08_27_082229_create_apar_reparasi_photos_table.php (contoh nama file)

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
        Schema::create('apar_reparasi_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspeksi_id')->constrained('apar_inspections')->onDelete('cascade');
            $table->foreignId('item_check_id')->constrained('item_checks')->onDelete('cascade');
            $table->string('foto_path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apar_reparasi_photos');
    }
};