    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up(): void
        {
            Schema::create('apar_inspections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('master_apar_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->date('date');
                // Menambahkan kolom foto_path
                $table->string('final_foto_path')->nullable();
                $table->timestamps();
            });
        }
        public function down(): void
        {
            Schema::dropIfExists('apar_inspections');
        }
    };
    