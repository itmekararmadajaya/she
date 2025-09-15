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
        Schema::table('master_apars', function (Blueprint $table) {
            $table->string('status_penggunaan')->default('BELUM DIPAKAI')->after('status_apar');
        });
    }

    public function down()
    {
        Schema::table('master_apars', function (Blueprint $table) {
            $table->dropColumn('status_penggunaan');
        });
    }
};
