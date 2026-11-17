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
        Schema::table('alumnos', function (Blueprint $table) {
            // Ciclo que cursa el alumno de academia (null para ESCOLAR).
            $table->foreignId('id_ciclo')->nullable()->after('id_grado')
                ->constrained('ciclos_academia', 'id_ciclo')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropForeign(['id_ciclo']);
            $table->dropColumn('id_ciclo');
        });
    }
};
