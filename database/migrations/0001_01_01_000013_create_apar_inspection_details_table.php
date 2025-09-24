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
        Schema::create('apar_inspection_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apar_inspection_id')->constrained('apar_inspections')->onDelete('cascade');
            $table->foreignId('item_check_id')->constrained('item_checks')->onDelete('cascade');
            $table->string('value', 5);
            $table->string('remark')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apar_inspection_details');
    }
};
