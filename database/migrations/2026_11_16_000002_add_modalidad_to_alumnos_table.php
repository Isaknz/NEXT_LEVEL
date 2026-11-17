<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La modalidad del alumno deja de deducirse del prefijo del código
 * ('AC%'): pasa a ser una columna propia, igual que en matrículas.
 * Los alumnos existentes con código AC… se marcan como ACADEMIA.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->enum('modalidad', ['ESCOLAR', 'ACADEMIA'])
                ->default('ESCOLAR')
                ->after('estado');
        });

        DB::table('alumnos')
            ->where('codigo', 'LIKE', 'AC%')
            ->update(['modalidad' => 'ACADEMIA']);
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropColumn('modalidad');
        });
    }
};
