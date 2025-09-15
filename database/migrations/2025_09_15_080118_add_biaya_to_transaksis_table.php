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
        Schema::table('transaksis', function (Blueprint $table) {
            $table->decimal('biaya', 10, 2)->nullable()->after('biaya_id'); // Ganti 'kolom_sebelumnya' dengan nama kolom yang ingin Anda letakkan di depannya
        });
    }
};