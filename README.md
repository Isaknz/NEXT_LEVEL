# Next Level School

Sistema de gestión escolar y financiera para el colegio/academia "Next Level". Permite administrar alumnos, apoderados, matrículas, pagos, gastos, cajas y una bitácora de auditoría de todos los movimientos.

## Tecnologías

- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Blade + Tailwind CSS + Alpine.js (Vite)
- **Base de datos:** MySQL 8
- **Autenticación:** personalizada (login propio con roles)

## Roles

| Rol        | Alcance                                                                  |
|------------|--------------------------------------------------------------------------|
| `admin`    | Acceso total (usuarios, auditoría, anular pagos/gastos)                  |
| `gerente`  | Reportes y anular pagos/gastos                                           |
| `secretaria` | Registro de alumnos, matrículas, pagos y gastos                        |

## Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate

# Configura los datos de MySQL en .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
php artisan migrate
php artisan db:seed

npm install
npm run build
```

El seeder crea los catálogos base (niveles, grados, cajas, conceptos, categorías) y un usuario administrador:

- Email: `admin@nextlevel.edu.pe`
- Password: `admin123`

> Cambia la contraseña del administrador después del primer ingreso.

## Desarrollo

```bash
npm run dev   # Vite
php artisan serve
```

O con el script integral:

```bash
composer dev
```

## Tests

```bash
composer test
```

Los tests cubren la lógica financiera crítica: registro y anulación de pagos, saldos de cuentas por cobrar, gastos y restricciones de matrículas.

## Módulos

- **Alumnos y Apoderados:** maestro de personas con estado y soft-delete.
- **Matrículas:** escolar (grado) o academia (ciclo), con validación de matrículas activas duplicadas.
- **Pagos:** se aplican a cuentas por cobrar con control de saldos; generan movimiento de caja y comprobante. Solo admin/gerente pueden anular.
- **Gastos:** registro con comprobante en PDF/imagen y movimiento de caja (EGRESO). Solo admin/gerente pueden anular.
- **Cuentas por Cobrar:** estados `PENDIENTE`, `PARCIAL`, `PAGADA` calculados a partir de los pagos confirmados.
- **Cajas:** efectivo, banco o billetera digital (Yape/PLIN).
- **Auditoría:** registro de todas las acciones (`CREAR`, `ACTUALIZAR`, `ANULAR`, `ELIMINAR`, `INICIAR_SESION`, `CERRAR_SESION`, …) con valores antes/después en JSON.

## Estructura relevante

```
app/Http/Requests/      # Validaciones por entidad (Form Requests)
app/Models/             # Modelos Eloquent con scopes de filtrado (scopeFiltrar)
app/Policies/           # Autorizaciones finas (anulación de pagos/gastos)
database/migrations/    # Esquema completo versionado
database/seeders/       # Catálogos + usuario admin
tests/Feature/          # Tests de flujos críticos
```

## Buenas prácticas incluidas

- Form Requests para validación centralizada.
- Errores de dominio (saldos, montos) como `ValidationException`; errores inesperados se registran en logs y muestran mensajes genéricos.
- `throttle:login` (5 intentos/minuto) contra fuerza bruta.
- Transacciones en operaciones financieras con rollback.
- Lógica de saldos en el modelo (`CuentaPorCobrar::recalcularEstado()`), consistente entre alta y anulación.