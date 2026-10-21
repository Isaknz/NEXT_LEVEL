# Next Level School — Documentación del Proyecto

> Documento técnico completo para evaluación. Incluye alcance, funcionalidad implementada, arquitectura, seguridad, estado de pruebas y **evaluación honesta de completitud**.

---

## 1. Resumen ejecutivo

| Indicador | Valor |
|---|---|
| **Nombre** | Next Level School — Sistema de gestión escolar y financiera |
| **Versión de plataforma** | Laravel 12 / PHP 8.2+ / MySQL 8 |
| **Rutas registradas** | 133 |
| **Controladores** | 22 |
| **Modelos Eloquent** | 23 |
| **Vistas Blade** | 97 |
| **Migraciones** | 32 |
| **Form Requests** | 19 |
| **Policies** | 2 |
| **Factories** | 13 |
| **Archivos de prueba** | 20 |
| **Tests / aserciones** | **223 / 627** (verdes en SQLite y MySQL) |
| **Roles soportados** | 4 (`admin`, `gerente`, `secretaria`, `cajero`) |
| **Acciones auditadas** | 14 |
| **Rama oficial** | `master` |
| **Commits** | 9+ en `master` |
| **CI/CD** | GitHub Actions: `.github/workflows/ci.yml` (PHP 8.2 y 8.3 + MySQL 8) |

**Estado actual:** funcional, sin errores de compilación, rutas verificadas y con autenticación endurecida.

---

## 1.5 Rama oficial

**Rama oficial de desarrollo y entrega:** `master`

La rama `main` contiene únicamente el esqueleto inicial de Laravel y **no debe utilizarse** para despliegues. El sistema real se encuentra en `master`.

Antes de clonar para evaluación o despliegue, utiliza siempre:

```bash
git clone --branch master https://github.com/Isaknz/NEXT_LEVEL.git
```

Para obtener el commit exacto de esta entrega, consulta el historial de Git o la etiqueta `v1.0.0` cuando esté publicada.

- **Backend:** Laravel 12 (PHP 8.2+)
- **Frontend:** Blade + Tailwind CSS + Alpine.js, compilado con **Vite 7.3.6**
- **Base de datos:** MySQL 8 (SQLite `:memory:` para pruebas)
- **Autenticación:** personalizada (login propio con roles, no es Breeze)
- **Reportes:** `barryvdh/laravel-dompdf ^3.0` (PDF), `maatwebsite/excel ^3.1` (Excel), CSV nativo
- **Tipografía:** Figtree
- **Íconos:** Font Awesome 6.5.1

---

## 3. Módulos funcionales

| Módulo | Rutas | Cobertura |
|---|---|---|
| Dashboard | `dashboard` | Indicadores, pagos por método, totales |
| Alumnos | CRUD completo + `show` | Alta, edición, consulta, baja |
| Apoderados | CRUD completo + `show` | Maestro de representantes |
| Matrículas | CRUD completo + `show` | Escolar (grado) o academia (ciclo), con registro de pagos iniciales; bloqueo de matrículas activas duplicadas |
| Pagos | `index/create/store/show/destroy/anular/comprobante` | Aplicación a cuentas por cobrar con control de saldos, movimiento de caja y comprobante en PDF |
| Cuentas por Cobrar | CRUD + `anular` | Estados `PENDIENTE` / `PARCIAL` / `PAGADA` calculados, vencimientos, anulación con motivo |
| Gastos | CRUD completo + `anular` | Registro con comprobante, movimiento de caja (EGRESO) |
| Cajas | `index/create/store/edit/show/update/destroy/aperturar/cerrar/ajuste` | Cierre con saldo final, apertura, ajuste de caja |
| Catálogos | niveles, grados, facultades, periodos, conceptos de cobro, categorías de gasto, ciclos | CRUD con validación de duplicados y de dependencias |
| Reportes | `index/deudores/vencidos/export (CSV · Excel · PDF)` | Deudores, vencidos, pagos |
| Usuarios | CRUD completo + `show` | Gestión de cuentas y roles |
| Auditoría | `index/show/limpiar` | Bitácora con valores antes/después |
| Perfil | `edit/update/delete/change-password` | Cuenta propia |

---

## 4. Roles y permisos

**Fuente única de verdad:** `app/Support/Permisos.php`. El middleware de permisos y el menú lateral consultan esta misma clase, de modo que **el menú nunca muestra un módulo que luego devuelve 403**.

| Rol | Alcance | Restricciones |
|---|---|---|
| `admin` | Acceso total | — |
| `gerente` | Todo excepto gestión de usuarios | Solo **lista y consulta** usuarios; no puede crear, editar ni eliminar (bloqueado para impedir la auto-promoción a `admin`) |
| `secretaria` | Alumnos, apoderados, matrículas, pagos, cuentas por cobrar, conceptos, categorías, cajas, periodos, niveles, grados, facultades, ciclos | **No** puede anular pagos, gastos ni cuentas; **no** accede a usuarios, reportes ni auditoría |
| `cajero` | `pagos.create`, `pagos.store`, `gastos.create`, `gastos.store`, `cajas.index`, `cajas.show` | Solo escritura de movimientos y lectura de caja |

---

## 5. Seguridad

- **Sin registro público.** Rutas `register` eliminadas y eliminado el enlace desde el login. El alta de cuentas es exclusiva del administrador.
- **Clave temporal obligatoria.** Middleware `DebeCambiarClave` (alias `clave.temporal`): mientras `password_changed_at` sea `NULL`, el usuario queda bloqueado y solo puede cambiar su contraseña o cerrar sesión. Se aplica a todas las rutas autenticadas con enmienda explícita a `password.change`, `password.update` y `logout`.
- **Recuperación de contraseña**
  - Token de un solo uso, vigencia de 60 minutos.
  - Respuesta genérica (no revela si el correo existe).
  - Solo cuentas activas.
  - `throttle:6` intentos por minuto.
  - Auditoría de `SOLICITAR_RESET` y `RESTABLECER_CLAVE`.
  - Estampa `password_changed_at` al restablecer.
  - Correos y mensajes íntegramente en español (sin claves crudas como `validation.required`).
- **Fuerza bruta:** `throttle:login` en el ingreso.
- **Principio de mínimo privilegio:** los permisos no se derivan de rutas sueltas sino de la matriz centralizada.
- **Errores genéricos en producción:** `APP_DEBUG=false`.
- **Credenciales fuera del repositorio:** `.env` ignorado; `.env.example` solo con placeholders.
- **Contraseñas** con `password_hash` (bcrypt) y transacciones en operaciones financieras con *rollback*.

---

## 6. Auditoría

Tabla `registro_movimientos` con 14 acciones permitidas:

```
CREAR, ACTUALIZAR, ANULAR, ELIMINAR, VER, EXPORTAR, IMPRIMIR,
INICIAR_SESION, CERRAR_SESION, CERRAR_CAJA, REABRIR_CAJA,
AJUSTE_CAJA, SOLICITAR_RESET, RESTABLECER_CLAVE
```

Registra usuario, ruta, módulo y valores **antes/después** en JSON. El registro conserva la fila aunque el usuario sea eliminado (`user_id` queda `NULL` en lugar de perder la evidencia).

---

## 7. Identidad visual (última actualización)

Pantalla de login rediseñada con identidad institucional tomada del archivo de logo real:

| Elemento | Color |
|---|---|
| Fondo exterior | `#05122a` (marino profundo) |
| Panel izquierdo (identidad) | `#002653` → `#01325d` |
| Panel derecho (formulario) | `#fbfdff` (blanco azulado) |
| Acento | `#fb000f` (rojo del logo, uso moderado) |

- **Panel izquierdo:** logo real, `NEXT LEVEL / SCHOOL`, lema, mensaje "Educación que impulsa el **futuro.**", 4 módulos en píldoras discretas, flecha ascendente decorativa al 5%, pie con copyright.
- **Panel derecho:** jerarquía de título, campos con ícono y *placeholder*, **mostrar/ocultar contraseña**, "Recuérdame", enlace de recuperación, botón **"Ingresar →"**, divisor y avisos de seguridad.
- **Recuperación y nueva clave:** comparten la misma identidad y estructura de una sola columna.
- **Responsive:** dos columnas desde 1024 px; en tablet/celular el panel izquierdo se reduce a su cabecera. Sin scroll horizontal.
- Corregido un defecto preexistente: el logo apuntaba a una ruta inexistente y se ocultaba en silencio (`onerror`), lo que también afectaba al menú lateral.

---

## 8. Instalación

```bash
composer install
cp .env.example .env
php artisan key:generate
# Configura MySQL en .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
php artisan migrate
php artisan db:seed

npm install
npm run build
```

O con el script único:

```bash
composer setup
```

**Desarrollo:**

```bash
composer dev      # servidor, cola, logs y Vite en paralelo
composer test     # limpia configuración y ejecuta la suite
```

### Cuentas de prueba

Creadas por `database/seeders/UsuariosSeeder.php` (seeder idempotente, no pisa claves ya cambiadas):

| Correo | Rol |
|---|---|
| `admin@nextlevel.edu.pe` | `admin` |
| `gerente@nextlevel.edu.pe` | `gerente` |
| `secretaria@nextlevel.edu.pe` | `secretaria` |
| `cajero@nextlevel.edu.pe` | `cajero` |

> Las tres se crean con **clave temporal** (`password_changed_at = NULL`), por lo que obligan a cambiarla en el primer ingreso. Las contraseñas están definidas en el seeder; no se documentan aquí a propósito.

---

## 9. Configuración requerida en `.env`

| Clave | Propósito |
|---|---|
| `APP_LOCALE` / `APP_FALLBACK_LOCALE` | Idioma (`es` en la interfaz) |
| `APP_DEBUG` | **`false`** en producción |
| `DB_*` | Conexión MySQL |
| `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION` | SMTP para el enlace de recuperación |
| `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | Remitente |

---

## 10. Pruebas

```bash
php artisan test
```

**229 tests · 642 aserciones** — ejecutado y verificado tanto en **SQLite** como en **MySQL real**.

| Archivo de prueba | Cobertura |
|---|---|
| `AuthTest`, `AuthenticationTest` | Login correcto/incorrecto, logout, cambio de clave, cuentas desactivadas, auditoría de sesión |
| `RegistroPublicoTest` | Registro público inexistente, sin enlace de registro, gestión de cuentas solo por admin, bloqueo por clave temporal |
| `RestablecerClaveTest` | Enlace genérico, vigencia y un solo uso, no enumera correos, throttling, auditoría, mensajes en español |
| `LoginDesignTest` | Contrato visual de las 3 pantallas de acceso |
| `LogoTest` | El logo resuelve a un archivo existente y no vacío |
| `PermisosTest` | Matriz completa de permisos por rol y comportamiento del menú |
| `RouteHealthTest` | Todas las rutas GET responden; comprobantes y reportes generan |
| `UsuariosSemillaTest` | Seeder idempotente y bloqueo por clave temporal vía HTTP |
| `MatriculaTest`, `PagoTest`, `CuentaPorCobrarTest`, `GastoTest`, `CajaCrudTest` | Lógica financiera crítica, transacciones, FK y duplicados |
| `CatalogosCrudTest`, `AlumnoTest`, `DashboardTest`, `ReporteTest` | CRUD, catálogos, indicadores y exportaciones |
| `RespaldoTest` | Política de retención y mensajes de los comandos de respaldo |

### Integración continua

`.github/workflows/ci.yml` corre en cada *push* y *pull request* hacia `master`:

1. `composer validate --strict` e `composer install`
2. `composer audit` (vulnerabilidades en dependencias de producción)
3. `npm ci` y `npm audit --omit=dev --audit-level=high`
4. `npm run build` con Vite (obligatorio: las vistas usan `@vite`)
5. Migraciones sobre **MySQL 8** y `php artisan test` en **PHP 8.2 y 8.3**

Las variables de entorno de esos pasos sobreescriben el `DB_CONNECTION=sqlite` de
`phpunit.xml`, por lo que ese archivo **no se modifica**: en local `php artisan test`
sigue corriendo en SQLite con su base en memoria y nunca toca la base de desarrollo.

Aún **no** forman parte del CI: `pint --test` (falta reformatear ~110 archivos),
análisis estático (Larastan), CodeQL y Dependabot.

### Respaldo y restauración

La base de datos se respalda con comandos Artisan, portables entre Windows y Linux:

```bash
php artisan respaldo:crear                  # volcado .sql.gz + manifiesto con SHA-256
php artisan respaldo:restaurar <archivo> --destino=base_de_ensayo
php artisan respaldo:restaurar <archivo> --forzar   # sobre la base configurada
```

El respaldo verifica su integridad, conserva 14 copias diarias más 13 semanales y
compara las filas restauradas con el manifiesto. Procedimiento completo, RPO/RTO,
programación y *runbook* en [`docs/backup-restore.md`](docs/backup-restore.md).

---

## 11. Estructura del código

```
app/Http/Controllers/    22 controladores
app/Http/Requests/       19 Form Requests (validación centralizada)
app/Http/Middleware/     CheckRole, DebeCambiarClave, RolePermissionMiddleware
app/Models/              23 modelos con scopes de filtrado
app/Policies/            2 policies (anulación de pagos/gastos)
app/Support/Permisos.php Fuente única de verdad de permisos
app/Support/Respaldos/   Retención y cliente de binarios MySQL
docs/                    backup-restore.md (procedimiento operativo)
database/migrations/     32 migraciones versionadas
database/seeders/        Catálogos + cuentas de roles
resources/views/         97 vistas Blade
tests/Feature/           20 archivos de prueba
```

---

## 12. Historial de Git

| Commit | Mensaje |
|---|---|
| `9316b92` | Complete Next Level school management system |
| `c7be0d0` / `c5a21b5` | Segunda actualizacion |
| `ec83982` | Endurece acceso, permisos y restablecimiento de clave |
| `8b8b6d5` | Actualiza .env.example con la configuracion real de correo e idioma |
| `e845116` | **Cuarta actualización NEXTLEVEL Isaac** |
| `cf3f800` | Configura CI con GitHub Actions y repara composer.lock |

---

## 13. Evaluación de completitud

### 13.1 Lo que ya está completo

- ✅ **CRUD íntegro** de los 13 módulos funcionales, con validación, *soft* estado y mensajes en español.
- ✅ **Ciclo financiero completo:** matrícula → cuenta por cobrar → pago → movimiento de caja → comprobante PDF, con recálculo de estados y *rollback* ante error.
- ✅ **Control de caja:** apertura, cierre con saldo final, ajuste y anulaciones restringidas.
- ✅ **4 roles** con matriz de permisos centralizada y sincronizada entre el menú y las respuestas 403.
- ✅ **Seguridad de acceso endurecida:** sin registro público, clave temporal obligatoria, recuperación por token con *throttle*, *throttle* de login, debug desactivado y credenciales fuera de Git.
- ✅ **Auditoría completa** en las 14 acciones del dominio, con evidencia ante borrados.
- ✅ **Reportes exportables** en CSV, Excel y PDF, más vistas de deudores y vencidos.
- ✅ **Idioma** íntegramente español (interfaz, validaciones y correos).
- ✅ **Pruebas:** 223 tests / 625 assertions verdes en SQLite **y** MySQL real, incluida la verificación de que toda ruta GET responde.
- ✅ **Respaldo y restauración** con comandos Artisan, verificación SHA-256, retención automática y ciclo de restauración ensayado (28/28 tablas idénticas), documentado en `docs/backup-restore.md`.
- ✅ **Interfaz de acceso rediseñada** con identidad institucional y comportamiento responsive.
- ✅ **Árbol de Git limpio** y sincronizado con el remoto.

### 13.2 Pendientes detectados

| # | Pendiente | Impacto | Tipo |
|---|---|---|---|
| 1 | **La rama por defecto de GitHub es `main`, que contiene únicamente el esqueleto inicial de Laravel** (`Primer commit de mi proyecto laravel xd`); todo el sistema real vive en `master`. Hay que cambiar la rama por defecto desde *Settings → Branches* antes de compartir el enlace del repositorio. | **Alto** | Entrega |
| 2 | **Código muerto:** `resources/views/auth/register.blade.php` y `welcome.blade.php` no tienen ruta asignada. | Bajo | Limpieza |
| 3 | **No se siembra cuenta de `cajero`**, aunque el rol ya está permitido en la matriz de permisos. | Bajo | Datos |
| 4 | Rutas y vistas heredadas de Breeze siguen presentes (`verify-email`, `confirm-password`) sin uso evidente en el flujo de ingreso. | Bajo | Limpieza |
| 5 | **No hay migración ni carga de datos de producción**; los *seeders* son de desarrollo/demostración. | Medio | Operación |
| 6 | **No hay proceso de despliegue documentado**; el plan de respaldo ya está implementado y documentado en `docs/backup-restore.md`. | Medio | Operación |
| 7 | El README anterior **no correspondía con el código** (mencionaba 3 roles en vez de 4 y una contraseña de acceso que ya no es la vigente). Corregido en este documento. | — | Documentación |
| 8 | No hay seguridad de segundo factor (2FA) ni política de rotación de contraseñas. | Bajo | Mejora |

### 13.3 Veredicto

**Funcionalmente completo para su alcance.** El sistema cubre de punta a punta la operación académica y financiera de un colegio/academia: maestros, matrículas, cobranzas, caja, reportes, auditoría y control de accesos por rol. No queda ninguna funcionalidad del alcance descrito a medio construir, todas las rutas responden y la suite de pruebas está completa y en verde en dos motores de base de datos.

**No está completo como entrega de ingeniería.** Lo que falta no es funcionalidad sino proceso y operación: fijar la rama por defecto del repositorio, limpieza de código heredado, análisis estático en el CI, datos de producción y proceso de despliegue. Son tareas acotadas y de bajo riesgo, ninguna de ellas bloquea el uso del sistema.

> **Conclusión:** apto para usarse y demostrarse hoy. Antes de considerarlo "entregado en producción" conviene cerrar los puntos 1, 5 y 6 de la tabla de pendientes.

---

## 14. Lista de verificación rápida

- [x] Sin errores de compilación
- [x] Login funcional (acceso correcto e incorrecto)
- [x] Campos de correo y contraseña operativos con valor previo y *placeholder*
- [x] Mostrar/ocultar contraseña operativo
- [x] Casilla "Recuérdame" operativa
- [x] Enlace de recuperación de contraseña operativo
- [x] Botón de inicio de sesión operativo
- [x] Diseño responsive (desktop, laptop, tablet, celular)
- [x] Sin scroll horizontal
- [x] Registro público deshabilitado
- [x] Bloqueo por clave temporal activo
- [x] Permisos por rol verificados
- [x] Pruebas en verde en SQLite y MySQL
- [x] Integración continua
- [x] Plan de respaldo
- [ ] Cuenta `cajero` de prueba
- [ ] Limpieza de vistas heredadas
- [ ] Plan de despliegue
