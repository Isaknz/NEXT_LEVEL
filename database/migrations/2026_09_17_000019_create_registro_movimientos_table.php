<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('registro_movimientos')) {
            return;
        }

        Schema::create('registro_movimientos', function (Blueprint $table) {
            $table->id('id_registro');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('accion', ['CREAR', 'ACTUALIZAR', 'ANULAR', 'ELIMINAR', 'VER', 'EXPORTAR', 'IMPRIMIR', 'INICIAR_SESION', 'CERRAR_SESION']);
            $table->string('modulo', 80);
            $table->string('entidad', 80)->nullable();
            $table->unsignedBigInteger('entidad_id')->nullable();
            $table->string('descripcion', 500)->nullable();
            $table->longText('filtros')->nullable();
            $table->longText('valores_anteriores')->nullable();
            $table->longText('valores_nuevos')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['modulo', 'entidad', 'entidad_id'], 'idx_auditoria_modulo_entidad');
            $table->index(['user_id', 'created_at'], 'idx_auditoria_usuario_fecha');

            $table->foreign('user_id', 'fk_auditoria_user')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE registro_movimientos ADD CONSTRAINT registro_movimientos_chk_1 CHECK (json_valid(`filtros`))');
            DB::statement('ALTER TABLE registro_movimientos ADD CONSTRAINT registro_movimientos_chk_2 CHECK (json_valid(`valores_anteriores`))');
            DB::statement('ALTER TABLE registro_movimientos ADD CONSTRAINT registro_movimientos_chk_3 CHECK (json_valid(`valores_nuevos`))');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('registro_movimientos');
    }
};