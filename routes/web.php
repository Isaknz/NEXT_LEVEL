<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\ApoderadoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\PeriodoAcademicoController;
use App\Http\Controllers\NivelController;
use App\Http\Controllers\GradoController;
use App\Http\Controllers\FacultadController;
use App\Http\Controllers\CicloAcademiaController;
use App\Http\Controllers\ConceptoCobroController;
use App\Http\Controllers\CategoriaGastoController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CuentaPorCobrarController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('throttle:login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register');
    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.reset.update');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('/confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::post('/verify-email/email', [EmailVerificationNotificationController::class, 'store'])->name('verification.send');
    Route::get('/verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])->name('verification.verify');
    Route::get('/email/verify', [EmailVerificationPromptController::class, '__invoke'])->name('verification.notice');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/password/change', [AuthController::class, 'showChangePassword'])->name('password.change');
    Route::put('/password/change', [AuthController::class, 'updatePassword'])->name('password.update');

    Route::middleware(['role.permission'])->group(function () {
        Route::resource('alumnos', AlumnoController::class);
        Route::resource('apoderados', ApoderadoController::class);
        Route::resource('matriculas', MatriculaController::class);
        Route::resource('pagos', PagoController::class);
        Route::post('/pagos/{pago}/anular', [PagoController::class, 'anular'])->name('pagos.anular');
        Route::get('/pagos/{pago}/comprobante', [PagoController::class, 'comprobante'])->name('pagos.comprobante');
        Route::resource('gastos', GastoController::class);
        Route::post('/gastos/{gasto}/anular', [GastoController::class, 'anular'])->name('gastos.anular');
        Route::resource('cuentas-por-cobrar', CuentaPorCobrarController::class);
        Route::resource('cajas', CajaController::class);
        Route::post('/cajas/{caja}/cerrar', [CajaController::class, 'cerrar'])->name('cajas.cerrar');
        Route::post('/cajas/{caja}/aperturar', [CajaController::class, 'aperturar'])->name('cajas.aperturar');
        Route::post('/cajas/{caja}/ajuste', [CajaController::class, 'ajuste'])->name('cajas.ajuste');
        Route::resource('periodos', PeriodoAcademicoController::class);
        Route::resource('niveles', NivelController::class);
        Route::resource('grados', GradoController::class);
        Route::resource('facultades', FacultadController::class);
        Route::resource('ciclos', CicloAcademiaController::class);
        Route::resource('conceptos', ConceptoCobroController::class);
        Route::resource('categorias', CategoriaGastoController::class);
        Route::get('/reportes/deudores', [ReporteController::class, 'deudores'])->name('reportes.deudores');
        Route::get('/reportes/vencidos', [ReporteController::class, 'vencidos'])->name('reportes.vencidos');
        Route::get('/reportes/export/excel', [ReporteController::class, 'exportExcel'])->name('reportes.export.excel');
        Route::get('/reportes/export', [ReporteController::class, 'exportExcel'])->name('reportes.export');
        Route::get('/reportes/export/pdf', [ReporteController::class, 'exportPdf'])->name('reportes.export.pdf');
        Route::resource('reportes', ReporteController::class);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware(['check.role:admin,gerente'])->group(function () {
        Route::resource('users', UserController::class);
        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::get('/auditoria/{movimiento}', [AuditoriaController::class, 'show'])->name('auditoria.show');
        Route::post('/auditoria/limpiar', [AuditoriaController::class, 'limpiar'])->name('auditoria.limpiar');
    });
});
