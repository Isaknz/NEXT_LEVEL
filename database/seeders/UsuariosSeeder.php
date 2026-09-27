<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Cuentas de acceso del personal.
 *
 * El sistema es de lista cerrada: no hay registro público, así que estas
 * cuentas son el punto de partida y luego el administrador crea una por
 * persona desde el módulo Usuarios.
 *
 * IMPORTANTE: las tres se crean con password_changed_at = null, y el
 * middleware DebeCambiarClave bloquea todo el sistema hasta que cada usuario
 * reemplace su clave temporal. Compartir "nextlevel2026" entre gerente y
 * secretaria hacía que la auditoría no pudiera distinguir quién cobró,
 * anuló o editó un registro.
 */
class UsuariosSeeder extends Seeder
{
    /**
     * Claves temporales iniciales. El middleware obliga a cambiarlas
     * en el primer ingreso, así que no quedan como credencial vigente.
     */
    private const CUENTAS = [
        [
            'email' => 'admin@nextlevel.edu.pe',
            'nombre' => 'Administrador',
            'password' => 'senati2026',
            'role' => 'admin',
        ],
        [
            'email' => 'gerente@nextlevel.edu.pe',
            'nombre' => 'Gerente',
            'password' => 'nextlevel2026',
            'role' => 'gerente',
        ],
        [
            'email' => 'secretaria@nextlevel.edu.pe',
            'nombre' => 'Secretaría',
            'password' => 'nextlevel2026',
            'role' => 'secretaria',
        ],
    ];

    public function run(): void
    {
        foreach (self::CUENTAS as $cuenta) {
            $existente = User::where('email', $cuenta['email'])->first();

            if ($existente) {
                // No se pisa una clave que el usuario ya haya cambiado.
                $existente->update([
                    'role' => $cuenta['role'],
                    'estado' => 'activo',
                ]);

                continue;
            }

            User::create([
                'nombre' => $cuenta['nombre'],
                'email' => $cuenta['email'],
                'password' => Hash::make($cuenta['password']),
                'role' => $cuenta['role'],
                'estado' => 'activo',
                'password_changed_at' => null,
            ]);
        }
    }
}
