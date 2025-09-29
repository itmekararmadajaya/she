<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('apar_inspeksi_photos', function (Blueprint $table) {
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
    public function down()
    {
        Schema::dropIfExists('apar_inspeksi_photos');
    }
};
