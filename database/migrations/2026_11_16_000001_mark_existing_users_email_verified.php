<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Los usuarios existentes ya operaban el sistema antes de que la
 * verificación de correo fuera obligatoria. Se marcan como verificados
 * para no bloquearlos; solo las cuentas creadas a partir de ahora deben
 * confirmar su correo.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // No hay forma de distinguir a quién se le asignó la marca en esta
        // migración; el down es deliberadamente vacío.
    }
};
