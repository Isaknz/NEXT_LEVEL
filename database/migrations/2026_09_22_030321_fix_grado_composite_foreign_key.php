<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE `matriculas` DROP FOREIGN KEY `fk_matriculas_grado_nivel`');

            Schema::table('matriculas', function (Blueprint $table) {
                $table->foreign('id_grado', 'fk_matriculas_grado_nivel')
                      ->references('id_grado')
                      ->on('grados')
                      ->onUpdate('cascade');
            });
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            Schema::table('matriculas', function (Blueprint $table) {
                $table->dropForeign(['id_grado']);
            });

            Schema::table('matriculas', function (Blueprint $table) {
                $table->foreign(['id_grado', 'id_nivel'], 'fk_matriculas_grado_nivel')
                      ->references(['id_grado', 'id_nivel'])
                      ->on('grados');
            });
        }
    }
};
