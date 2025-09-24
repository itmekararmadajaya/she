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
            $table->unsignedBigInteger('inspeksi_id');
            $table->unsignedBigInteger('item_check_id');
            $table->string('foto_path');
            $table->timestamps();

            $table->foreign('inspeksi_id')
                ->references('id')->on('apar_inspections')
                ->onDelete('cascade');

            $table->foreign('item_check_id')
                ->references('id')->on('item_checks')
                ->onDelete('cascade');
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