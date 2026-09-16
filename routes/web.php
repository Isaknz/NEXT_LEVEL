<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AlumnoController;
use App\Http\Controllers\ApoderadoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\MatriculaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\GastoController;

// Ruta principal → redirige al login
Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas de autenticación
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        $user = auth()->user();
        return view('dashboard', compact('user'));
    })->name('dashboard');

    // Alumnos
    Route::resource('alumnos', AlumnoController::class);

    // Apoderados
    Route::resource('apoderados', ApoderadoController::class);

    // Matrículas
    Route::resource('matriculas', MatriculaController::class);

    // Pagos
    Route::resource('pagos', PagoController::class);
    Route::post('/pagos/{pago}/anular', [PagoController::class, 'anular'])->name('pagos.anular');

    // Gastos
    Route::resource('gastos', GastoController::class);
    Route::post('/gastos/{gasto}/anular', [GastoController::class, 'anular'])->name('gastos.anular');

    // Rutas solo para admin y gerente
    Route::middleware(['check.role:admin,gerente'])->group(function () {
        Route::resource('users', UserController::class);

        // Auditoría
        Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
        Route::get('/auditoria/{movimiento}', [AuditoriaController::class, 'show'])->name('auditoria.show');
        Route::post('/auditoria/limpiar', [AuditoriaController::class, 'limpiar'])->name('auditoria.limpiar');
    });
});
