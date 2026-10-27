# Informe de reorganización: NEXT_LEVEL (sin matrículas locales)
Fecha: 2026-10-21 | Rama: refactor/integracion-matriculas | Commit: b596148

## 1. Resumen del estado actual
- Laravel 12, PHP 8.2.12. Rutas: 137. Tests actuales en base sqlite :memory: pasan 229/229 tras último fix.
- Hay un alumno (1) existente en BD local; matriculas(0), cuentas_por_cobrar(0), pagos(0), gastos(0). Datos reales financieros vacíos.
- Módulo de matrículas completamente implementado (CRUD + validaciones + creación de alumno inline + pagos iniciales + auditoría). 
- Otros módulos dependen fuertemente de matriculas para cuentas por cobrar/pagos/reportes/dashboard.

## 2. Archivos relacionados con matrícula
Rutas: routes/web.php (resource matriculas completo)
Controlador: app/Http/Controllers/MatriculaController.php
Request: app/Http/Requests/MatriculaRequest.php
Modelo: app/Models/Matricula.php
Vistas: resources/views/matriculas/{index,create,edit,show}.blade.php
Menú: resources/views/layouts/app.blade.php (link \"Matrículas\")
Factory: database/factories/MatriculaFactory.php
Migraciones: database/migrations/2026_09_17_000012_create_matriculas_table.php, 2026_09_22_030243_add_pagada_status_to_matriculas_table.php
Tests: tests/Feature/MatriculaTest.php + muchas referencias en tests/Feature/*.php (~138 referencias totales)

## 3. Dependencias con otros módulos
- Matricula -> Alumno, PeriodoAcademico, Nivel, Grado, CicloAcademia, User, Pago, CuentaPorCobrar
- CuentaPorCobrar pertenece a Matricula (id_matricula FK). PagoDetalle se vincula a CuentaPorCobrar/Pago (financiero puro en origen pero iniciado desde flujo de matrícula/pagos)
- PagoController (22 refs), CuentaPorCobrarController (13), ReporteController (9), DashboardController (5) usan Matricula/relaciones
- Alumno tiene hasMany Matricula (necesario para trazabilidad/histórico)
- Auditoría registra movimientos desde MatriculaController

## 4. Tablas y migraciones relacionadas
Tablas: matriculas, cuentas_por_cobrar (FK a matriculas), pagos (FK a matriculas), pago_detalles. 
Migraciones vigentes creadoras/ajustes para matriculas. No modificar migraciones existentes.

## 5. Tests afectados
- tests/Feature/MatriculaTest.php (8 tests) - CRUD + reglas + bloqueo de pagos al eliminar
- RouteHealthTest verifica rutas matriculas/{id}/edit/show
- Numerosos tests usan MatriculaFactory o crean vía POST /matriculas (Reportes/Pagos/Cajas/CuentasPorCobrar)
- Eliminar/desactivar flujo local rompería ~varios tests

## 6. Elementos seguros para desactivar
- Rutas create/store/edit/update/destroy de matriculas (mantener index/show solo lectura si necesario)
- Vistas create/edit + formularios inline de alumno desde matrícula
- Lógica de creación masiva/pagos iniciales en MatriculaController@store
- Botones \"Nueva matrícula\" / acciones de edición en menú/vistas
- Validaciones específicas de alta local en MatriculaRequest
Recomendado: desactivar vía Policy/Gate o comentar routes + ocultar UI primero

## 7. Elementos que no se deben tocar
- Modelos Matricula, Alumno, CuentaPorCobrar, Pago (core financiero)
- Relaciones entre ellos
- Migraciones
- Pagos, Gastos, Cajas, Reportes, Autenticación/Permisos
- Auditoría
- Tablas financieras existentes

## 8. Diseño recomendado de integración
- API privada/webhook POST con auth (token/secret/signature) + rate limit
- Servicio App\Services\MatriculaExternaService (idempotente, transaccional)
- Campos mínimos: codigo_externo, id_alumno (local/upsert por DNI/codigo), id_periodo, modalidad, id_nivel, id_grado/id_ciclo, fecha_matricula, estado='CONFIRMADA', origen='EXTERNO', metadata(json)
- Generar CuentaPorCobrar desde conceptos/config al recibir matrícula confirmada
- Idempotencia por (origen,codigo_externo)

## 9. Riesgos
- Romper dependencias financieras (cuentas_por_cobrar/pagos dependen de matriculas)
- RouteHealthTest + tests que crean matrículas fallarán si se desactivan rutas sin ajustar tests
- FK constraints
- Código asume existencia de relaciones completas

## 10. Plan de implementación en máximo 5 fases
Fase 1: Feature flag (matriculas_local_enabled=false), sin borrar código
Fase 2: Desactivar rutas create/store/edit/update/destroy + ocultar UI (mantener index/show)
Fase 3: Crear API/webhook + servicio + migración campos externos + validación
Fase 4: Ajustar tests relacionados (solo si necesario) y validar integración
Fase 5: Limpieza opcional tras validación aprobada (sin tocar migraciones/tablas)
