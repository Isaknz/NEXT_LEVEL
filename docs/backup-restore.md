# Respaldo y restauración

Guía operativa para respaldar y recuperar la base de datos de Next Level School.
El respaldo se ejecuta con comandos Artisan, así que funciona igual en Windows
(XAMPP) y en Linux.

---

## 1. Qué se respalda

| Elemento | ¿Se respalda? | Notas |
|---|---|---|
| Base de datos MySQL/MariaDB | Sí | Volcado completo (estructura + datos) comprimido con gzip. |
| Archivos de `storage/app` | Sí, si existen | Comprobantes o documentos subidos. Hoy la carpeta está vacía porque los PDF/Excel se generan al vuelo. |
| Código fuente | No (lo cubre Git) | El repositorio remoto es la fuente de verdad. |
| `.env` y `APP_KEY` | No | **Guardarlos aparte** en un gestor de secretos. Sin `APP_KEY` se invalidan sesiones y cookies cifradas. |
| Logs, caché, `vendor/`, `node_modules/` | No | Reconstruibles. |

Los comprobantes PDF y los reportes Excel/CSV se generan en cada petición y no se
persisten, por lo que no requieren respaldo propio.

---

## 2. Objetivos (RPO / RTO)

| Métrica | Objetivo | Cómo se cumple |
|---|---|---|
| **RPO** (pérdida máxima de datos) | 24 horas | Respaldo diario programado (§6). |
| **RTO** (tiempo máximo de recuperación) | 4 horas | Runbook de §8, con restauración ya ensayada. |

Son objetivos de referencia: deben confirmarse cuando se decida el hosting. Para
reducir el RPO por debajo de 24 h haría falta habilitar *binary logs* y
restauración a un punto en el tiempo (no incluido).

---

## 3. Crear un respaldo

```bash
php artisan respaldo:crear
```

Genera en `storage/app/respaldos/` (configurable):

| Archivo | Contenido |
|---|---|
| `nextlevel_db_AAAA-MM-DD_HHMMSS.sql.gz` | Volcado comprimido. |
| `nextlevel_db_AAAA-MM-DD_HHMMSS.json` | Manifiesto: fecha, BD, bytes, SHA-256, commit de Git y filas por tabla. |
| `nextlevel_db_AAAA-MM-DD_HHMMSS_storage.zip` | Solo si `storage/app` tiene archivos. |

Opciones:

- `--directorio=ruta` — destino distinto al configurado.
- `--sin-storage` — omite los archivos subidos.

El volcado usa `mysqldump --single-transaction --quick --routines --triggers
--events --hex-blob`, apto para InnoDB y sin bloquear la operación normal.

---

## 4. Restaurar un respaldo

```bash
# Ensayo en una base temporal (no toca los datos reales)
php artisan respaldo:restaurar storage/app/respaldos/nextlevel_db_2026-10-19_155004.sql.gz \
    --destino=nextlevel_prueba_restore
```

```bash
# Restauración real sobre la base configurada en .env
php artisan respaldo:restaurar storage/app/respaldos/nextlevel_db_2026-10-19_155004.sql.gz --forzar
```

Comportamiento:

1. Verifica el SHA-256 contra el manifiesto y **se niega** a restaurar un archivo alterado o incompleto.
2. Si el destino es la base en uso, exige `--forzar` (evita pisar producción por error).
3. Con `--destino`, crea la base si no existe.
4. Al terminar compara las filas de cada tabla con el manifiesto y avisa de cualquier diferencia.

La restauración es repetible: el volcado incluye `DROP TABLE IF EXISTS`, así que se
puede volver a ejecutar sobre la misma base.

---

## 5. Configuración

Todo se ajusta en `config/respaldos.php` mediante variables de entorno:

| Variable | Por defecto | Uso |
|---|---|---|
| `RESPALDOS_DIRECTORIO` | `storage/app/respaldos` | Carpeta de respaldos. En producción, fuera del árbol del proyecto. |
| `RESPALDOS_DIAS` | `14` | Días de respaldos diarios que se conservan. |
| `RESPALDOS_SEMANAS` | `13` | Semanas adicionales (una copia por semana). |
| `RESPALDOS_INCLUIR_STORAGE` | `true` | Incluir `storage/app` en el respaldo. |
| `MYSQLDUMP_BIN` | autodetectado | Ruta explícita a `mysqldump`. |
| `MYSQL_BIN` | autodetectado | Ruta explícita a `mysql`. |

**Selección de binarios.** En equipos con varias instalaciones de MySQL los
clientes no siempre son intercambiables: el cliente MariaDB de XAMPP no puede
autenticar contra un servidor MySQL 8 con `caching_sha2_password`. Por eso el
comando prueba varios candidatos (MySQL 8.x de `Program Files`, XAMPP y el
`PATH`) y usa el primero que logre conectarse; `MYSQLDUMP_BIN`/`MYSQL_BIN` lo
fuerzan a uno concreto. La herramienta utilizada se imprime al crear el respaldo.

Si se usa `php artisan config:cache`, hay que volver a ejecutar ese comando
después de cambiar estas variables.

La contraseña se pasa a los binarios por la variable `MYSQL_PWD`, no como
argumento, para que no quede visible en la lista de procesos.

---

## 6. Programación automática

**Linux (cron):**

```cron
15 2 * * * cd /var/www/nextlevel && php artisan respaldo:crear >> storage/logs/respaldo.log 2>&1
```

**Windows (Programador de tareas):**

- Acción: iniciar programa `C:\xampp\php\php.exe`
- Argumentos: `artisan respaldo:crear`
- Iniciar en: `C:\xampp\htdocs\nextlevel_`
- Frecuencia: diaria, 02:15.

La retención se aplica dentro de `respaldo:crear`: elimina los respaldos que
exceden la política (§7) y sus manifiestos/ZIP asociados. Conviene que el
respaldo no se solape con otro proceso pesado; no debe ejecutarse dos veces en
paralelo.

---

## 7. Retención

- Se conservan **todos** los respaldos de los últimos `RESPALDOS_DIAS` días (14).
- Para fechas anteriores se conserva **el más reciente de cada semana ISO**,
  hasta `RESPALDOS_SEMANAS` semanas (13).
- El resto se borra, junto con su `.json` y su `_storage.zip`.

Resultado: unas 27 copias (~3 meses), suficiente para errores detectados tarde
sin acumular archivos indefinidamente.

---

## 8. Copia fuera del servidor

Un respaldo en el mismo disco no protege de fallos de disco ni de ransomware.
Se recomienda la regla 3-2-1: tres copias, dos medios, una fuera del sitio.

```bash
# Sincronización simple a otro equipo
rclone sync /var/backups/nextlevel remoto:nextlevel-respaldos

# Alternativa con rsync
rsync -az --delete /var/backups/nextlevel/ usuario@otro-equipo:/respaldos/nextlevel/
```

Cifrado opcional antes de salir del servidor:

```bash
gpg --symmetric --cipher-algo AES256 --passphrase-file /ruta/clave respaldo.sql.gz
```

La clave de cifrado debe guardarse fuera del servidor. En Windows, `robocopy`
o `rclone` cubren la misma función.

---

## 9. Runbook de restauración

1. Avisar a los usuarios y poner la aplicación en mantenimiento:
   `php artisan down --secret="token-temporal"`.
2. **Respaldar el estado actual antes de tocar nada**:
   `php artisan respaldo:crear --directorio=storage/app/respaldos/previo`.
3. En un entorno de ensayo, restaurar primero con `--destino` y validar (§10).
4. Restauración real: `php artisan respaldo:restaurar <archivo> --forzar`.
5. Limpiar cachés y reiniciar workers:
   `php artisan optimize:clear` y reiniciar los servicios de cola si están activos.
6. Verificar el checklist de §10 y retirar el mantenimiento: `php artisan up`.
7. Restaurar `storage/app` desde el ZIP si el respaldo lo incluye.
8. Confirmar que `.env` y `APP_KEY` son los del entorno correcto.
9. Registrar el incidente y la fecha del respaldo utilizado.

---

## 10. Verificación posterior a una restauración

- [ ] Login con un usuario administrador y con un rol restringido.
- [ ] Número de usuarios y de alumnos coincide con lo esperado.
- [ ] Matrículas y cuentas por cobrar conservan sus estados.
- [ ] Pagos y comprobantes PDF se generan.
- [ ] Cajas abiertas/cerradas y sus movimientos.
- [ ] Reportes y exportaciones Excel/CSV/PDF.
- [ ] Registro de auditoría con las últimas acciones.
- [ ] Cola de trabajos sin pendientes anómalos.

Comprobaciones SQL útiles:

```sql
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM alumnos;
SELECT COUNT(*) FROM pagos;
SELECT SUM(monto) FROM pagos;
```

---

## 11. Prueba realizada (2026-10-19)

Ciclo completo ejecutado contra la base de desarrollo `reportesnextlevel_v2`
(MySQL 8.0.42):

1. `respaldo:crear` → 28 tablas, 74 filas, 7,2 KB comprimidos, SHA-256 registrado.
2. Restauración sobre la base viva sin `--forzar` → rechazada.
3. Respaldo alterado en un byte → rechazado por SHA-256.
4. `respaldo:restaurar --destino=nextlevel_prueba_restore` → 28 tablas y 74 filas.
5. Comparación independiente con `CHECKSUM TABLE`: 28/28 tablas idénticas.
6. Base temporal eliminada al terminar.

La suite automatizada (`tests/Feature/RespaldoTest.php`) cubre la política de
retención y los mensajes de error de ambos comandos.

---

## 12. Limitaciones conocidas

- Sin *binary logs*: no hay restauración a un punto exacto en el tiempo; el RPO
  real es el del último respaldo.
- Por defecto el respaldo queda en el mismo disco que la base; la copia externa
  (§8) es un paso manual hasta que se defina el hosting.
- `storage/app` está vacío hoy; el ZIP se generará cuando existan archivos.
- El destino de producción y su sistema operativo aún no están decididos; la
  programación (§6) debe adaptarse a ese entorno.
