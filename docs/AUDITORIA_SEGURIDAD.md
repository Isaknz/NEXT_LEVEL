# Auditoría de seguridad y correcciones — Next Level School

Documento generado tras una revisión completa del sistema (controladores,
requests, middleware, modelos, migraciones, comandos de respaldo y rutas).
Describe los problemas encontrados, las correcciones aplicadas y las
recomendaciones que quedan pendientes de decisión.

---

## 1. Correcciones aplicadas

### 1.1 Pagos (`app/Http/Controllers/PagoController.php`, `app/Http/Requests/PagoRequest.php`)

| Problema | Corrección |
|---|---|
| La misma cuenta por cobrar podía repetirse en `cuentas[]`, produciendo un sobrepago (cada línea se validaba contra el mismo saldo) | Regla `distinct` en `cuentas.*.id_cuenta` |
| Las validaciones de estado y pertenencia de la cuenta se hacían antes del `lockForUpdate`; una anulación concurrente podía aplicar el pago a una cuenta ya anulada | Dentro de la transacción, bajo lock, se repiten **todas** las validaciones (pertenencia, estado, saldo) |
| Se podía pagar una matrícula en estado ANULADA/RETIRADA/PAGADA | Se exige estado `ACTIVA` o `PENDIENTE` antes de procesar |
| `ValidationException` dentro de la transacción se convertía en error genérico | Se re-lanza antes del `catch (\Throwable)` |
| Anular pago/gasto solo bloqueaba caja `CERRADA`; una caja `INACTIVA` permitía anular | La condición ahora es `estado !== 'ACTIVA'` en pre-check y bajo lock |
| La impresión del comprobante no quedaba auditada | Se registra acción `IMPRIMIR` |
| `fecha_pago` aceptaba fechas futuras | `before_or_equal:today` |

### 1.2 Permisos (`app/Support/Permisos.php`)

- **Secretaría**: se reservaron todas las acciones de escritura sobre cajas
  (`cajas.create/store/edit/update/destroy/cerrar/aperturar/ajuste`). Antes
  podía crear ajustes manuales de ingreso/egreso (`AJUSTE_INGRESO` /
  `AJUSTE_EGRESO`) que inflan el saldo sin respaldo, cerrar y reabrir cajas.
  Conserva solo lectura (`cajas.index`, `cajas.show`).
- **`cuentas-por-cobrar.destroy`**: reservado para secretaría. Podía borrar
  una CxC permanentemente aunque tiene prohibido anularla.
- **`auditoria.limpiar`**: ahora es exclusivo del administrador. El gerente
  podía borrar todo el rastro de auditoría, incluidas sus propias acciones.

### 1.3 Cuentas por cobrar (`CuentaPorCobrarRequest`, `CuentaPorCobrarController`)

- `withValidator` exige `monto_original - descuento + recargo >= 0.01`.
  Antes un descuento mayor al original generaba una deuda negativa que el
  sistema marcaba como pagada.
- `destroy` refuerza la prohibición para `secretaria`/`cajero` (defensa en
  profundidad además del middleware).

### 1.4 Matrículas (`MatriculaRequest`, `MatriculaController`)

- El estado `PAGADA` ya no puede enviarse desde el formulario: solo el flujo
  de pagos lo asigna al saldar las cuentas. Se sigue aceptando al editar una
  matrícula que ya está `PAGADA`.
- El check de matrícula duplicada (alumno + periodo + modalidad) se movió
  dentro de la transacción con `lockForUpdate`, cerrando la race condition
  que permitía duplicados con requests simultáneos.

### 1.5 Cajas (`CajaRequest`, `CajaController`)

- Eliminada la regla `unique` sobre `estado` que nunca podía fallar (la PK ya
  es única). La protección real contra editar cajas cerradas vive en el
  controlador.
- `aperturar` ahora también reactiva cajas `INACTIVA` (antes quedaban
  bloqueadas para siempre al ponerlas inactivas desde el formulario).
- En `GastoController::update`, el movimiento de caja solo se actualiza si
  está `ACTIVO` (no se toca el historial de movimientos anulados).

### 1.6 Dashboard (`DashboardController`)

- `total_vencidos` ahora incluye cuentas `PARCIAL` (antes solo `PENDIENTE`,
  subestimando la deuda frente a los reportes).
- El saldo por caja se calcula con una sola consulta agregada en lugar de
  cargar todos los movimientos en memoria por cada caja (N+1).
- Filtros `desde/hasta/periodo/caja` ahora se validan.

### 1.7 Reportes (`ReporteController`)

- Las exportaciones a Excel/CSV/PDF quedan registradas en auditoría con la
  acción `EXPORTAR` (antes no dejaban rastro).
- El CSV neutraliza inyección de fórmulas (`=`, `+`, `-`, `@`, tab) en celdas
  de texto: un nombre de alumno malicioso podía ejecutar fórmulas al abrir
  el archivo en Excel.

### 1.8 Autenticación y auditoría

- `RateLimiter::for('login')` ahora limita también por email
  (10 intentos / 5 min), además del límite por IP: frena credential
  stuffing distribuido.
- `AuthController::updatePassword` acepta `password_current` y
  `current_password` (el parcial de Breeze usaba el segundo nombre).
- `AuditoriaController` incluye en el filtro las acciones nuevas
  (`CERRAR_CAJA`, `REABRIR_CAJA`, `AJUSTE_CAJA`, `SOLICITAR_RESET`,
  `RESTABLECER_CLAVE`).
- `UserStoreRequest`/`UserUpdateRequest` usan `Password::defaults()` en vez
  de `min:6`.

### 1.9 Migraciones y código muerto

- `2026_09_22_030243_add_pagada_status_to_matriculas_table.php`: la rama
  SQLite recreaba `pagos` **sin** `TRANSFERENCIA` en `metodo_pago`, dejando
  el esquema de tests distinto al de producción. Corregido en `up()` y `down()`.
- Eliminados (código muerto, ninguno estaba enrutado):
  - `routes/auth.php` (registro público de Breeze)
  - `app/Http/Controllers/Auth/RegisteredUserController.php`
  - `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
  - `app/Http/Controllers/Auth/PasswordController.php`
  - `app/Http/Controllers/MovimientoCajaController.php` (permitía crear
    movimientos de caja arbitrarios si se hubiera enrutado)
  - `resources/views/auth/register.blade.php`

### 1.10 Tests

- Nuevos tests de regresión:
  - `PagoTest::test_no_se_puede_repetir_la_misma_cuenta_en_un_pago`
  - `PagoTest::test_no_se_puede_pagar_una_matricula_anulada`
  - `CuentaPorCobrarTest::test_rechaza_descuento_que_deja_la_cuenta_en_cero_o_negativo`
  - `MatriculaTest::test_no_se_puede_crear_una_matricula_directamente_pagada`
- `PermisosTest` actualizado a la nueva matriz de permisos.
- `GastoTest` corregido para verificar el enlace autenticado al comprobante
  (disco privado) en lugar de la ruta pública anterior.

**Resultado:** suite completa en verde (272 tests, 733 aserciones).

---

## 2. Recomendaciones pendientes (requieren decisión)

### Seguridad

1. **Rotar credenciales de `.env`**: contiene la contraseña de MySQL
   (`root`) y una contraseña de aplicación de Gmail en texto plano. Si el
   proyecto se despliega o el archivo se comparte, deben rotarse. En
   producción usar variables de entorno del servidor, nunca el `.env` del
   repo.
2. **`APP_ENV` / `APP_DEBUG`**: verificar que en producción sean `production`
   y `false`. `ENLIGHTN_SKIP_ENVIRONMENT_SPECIFIC` no debe usarse fuera de
   local.
3. ~~**`SESSION_ENCRYPT=false`**~~ — **hecho**: activado en `.env` y
   `.env.example`. Invalida las sesiones activas una sola vez.
4. ~~**Verificación de email**~~ — **hecho**: el grupo principal de rutas
   ahora exige el middleware `verified`. Los usuarios existentes quedaron
   marcados como verificados por la migración
   `2026_11_16_000001_mark_existing_users_email_verified`; las cuentas nuevas
   reciben el correo de verificación al crearse (`UserController::store`) y
   pueden reenviarlo desde `/email/verify`.
5. **Respaldos**: `respaldo:restaurar` ahora **exige manifiesto con SHA-256**;
   sin él aborta y solo continúa con `--sin-verificar` (explícito). Pendiente:
   sacar los respaldos del servidor con `RESPALDOS_DIRECTORIO` — contienen
   datos personales completos.

#### Rotación de credenciales (manual, requiere tus cuentas)

El `.env` contiene credenciales reales que deben rotarse antes de cualquier
despliegue:

1. **Gmail**: la contraseña `MAIL_PASSWORD` es una *contraseña de aplicación*
   de `fredisaacperezaspajo@gmail.com`. Revócala en
   myaccount.google.com/apppasswords y genera una nueva; actualiza `.env`.
   Si el `.env` se compartió alguna vez (captura, chat, backup, commit
   accidental), revócala de inmediato.
2. **MySQL**: cambia la contraseña de `root` (o mejor, crea un usuario
   exclusivo para la app con permisos solo sobre `reportesnextlevel_v2`) y
   actualiza `DB_PASSWORD`.
3. En producción, define estas variables en el panel del hosting en lugar de
   subir el `.env` del proyecto.
6. **`phpstan_raw.txt`**: el análisis estático genera cientos de falsos
   positivos por la falta de anotaciones `@property`/ide-helper en los
   modelos. Regenerar la configuración o añadir `laravel-ide-helper` antes
   de usar phpstan como puerta de calidad.
7. **Comprobantes de gasto**: ya se sirven desde disco privado mediante
   `gastos.comprobante` (cambio en el working tree). Verificar que
   `storage:link` no exponga `storage/app/private` y que la ruta exija
   autenticación (está dentro del grupo `auth` + `role.permission`).

### Lógica de negocio (evaluar si aplica)

8. ~~**`fecha_pago` / `fecha_gasto`**~~ — **decidido**: se permite
   backdating (registrar cobros/gastos de días anteriores); solo se bloquean
   fechas futuras.
9. ~~**`MatriculaController::update`**~~ — **hecho**: una matrícula `PAGADA`
   ya no puede volver a `ACTIVA`/`PENDIENTE` por edición manual; solo pasa a
   estados terminales (`RETIRADA`/`ANULADA`/`FINALIZADA`) o sigue `PAGADA`.
10. **`exportPdf`** carga todos los pagos en memoria (`->get()`); con
    volúmenes grandes puede agotar la memoria. El Excel ya usa lotes.
11. **`registrarMovimiento()`** traga excepciones: si la tabla de auditoría
    falla, la operación continúa sin rastro. Considerar alertar o fallar en
    operaciones financieras.
12. **`RespaldoRestaurar`**: el SQL del respaldo se ejecuta tal cual;
    correcto para uso CLI, pero documentar que nunca debe exponerse por web.

---

## 3. Mapa de permisos actual

| Rol | Acceso |
|---|---|
| `admin` | Todo, incluida gestión de usuarios y limpieza de auditoría |
| `gerente` | Todo excepto gestión de usuarios (create/store/edit/update/destroy) y `auditoria.limpiar` |
| `secretaria` | Alumnos, apoderados, matrículas, pagos (sin anular), gastos (sin anular), CxC (sin anular ni destroy), conceptos, categorías, **cajas solo lectura**, catálogos. Sin reportes ni usuarios |
| `cajero` | `pagos.create/store`, `gastos.create/store`, `cajas.index/show` |

La fuente de verdad es `app/Support/Permisos.php`; el menú lateral y el
middleware consultan la misma clase, por lo que no pueden divergir.

---

## 4. Modalidad de alumno (ESCOLAR / ACADEMIA)

- `alumnos.modalidad` (migración `2026_11_16_000002`): los alumnos con
  `codigo LIKE 'AC%'` quedan como ACADEMIA; el resto ESCOLAR. Reemplaza la
  heurística por prefijo que usaban filtros y formularios.
- Matrícula: `ACADEMIA` exige nivel con `codigo='ACA'` + `id_ciclo`;
  `ESCOLAR` exige `id_grado` y rechaza el nivel ACADEMIA
  (`MatriculaRequest::withValidator`). El formulario preselecciona nivel y
  ciclo según la modalidad del alumno elegido.
- Registro de alumno: selector de modalidad; en ACADEMIA el grado se oculta
  (se asigna en la matrícula vía ciclo).
- Conceptos de cobro (catálogo): `MATRICULA`, `MATERIAL`, `EXAMEN` → AMBOS;
  `MENSUALIDAD` → ESCOLAR; `PENSION_CICLO` (tipo CICLO) → ACADEMIA.
  Regla de negocio: todos pagan matrícula; P/S mensualidad; academia por ciclo.
- Eliminado `app/Models/AlumnoAcademia.php` (modelo huérfano sin tabla).

### Ciclo del alumno de academia

- `alumnos.id_ciclo` (migración `2026_11_17_103628`): el alumno de academia
  puede registrar el ciclo que cursa al crear/editar su ficha. Es opcional;
  el ciclo definitivo se confirma en la matrícula (`id_ciclo` de
  `matriculas`). El formulario de matrícula preselecciona el ciclo del
  alumno cuando existe.
- Coherencia: ESCOLAR usa `id_grado` (ciclo se limpia), ACADEMIA usa
  `id_ciclo` (grado se limpia) — `AlumnoController::limpiarCamposPorModalidad`.
