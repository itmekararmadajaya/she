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
            $table->unsignedBigInteger('inspeksi_id'); // relasi ke apar_inspections
            $table->unsignedBigInteger('item_check_id'); // relasi ke item_check
            $table->string('foto_path'); // path foto
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
    public function down()
    {
        Schema::table('apar_inspeksi_photos', function (Blueprint $table) {
            $table->dropForeign(['item_check_id']);
            $table->dropColumn('item_check_id');
        });
    }
};
