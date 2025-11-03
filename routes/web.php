<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
// use App\Http\Controllers\SolicitudHeController;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('/authenticate', [AuthController::class, 'authenticate'])->name('authenticate');

Route::get('/test-login', function () {
    return view('test-login');
})->name('test-login');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('sistema/ingreso-he', 'sistema.ingreso-he')->name('sistema.ingreso-he');

    Volt::route('sistema/ciclo-aprobacion', 'sistema.ciclo-aprobacion')->name('sistema.ciclo-aprobacion');

    // Nueva ruta para aprobaciones masivas mejoradas
    Route::get('sistema/aprobaciones-masivas', \App\Livewire\Sistema\AprobacionesMasivas::class)
        ->middleware(['auth'])
        ->name('sistema.aprobaciones-masivas');

    Route::get('/demo-ciclo-aprobacion', \App\Livewire\DemoCicloAprobacion::class)
    ->middleware(['auth'])
    ->name('demo.ciclo-aprobacion');

    Route::get('sistema/ingreso-compensacion', \App\Livewire\Sistema\IngresoCompensacion::class)
    ->middleware(['auth'])
    ->name('sistema.ingreso-compensacion');

    // Nueva ruta para aprobaciones de compensación
    Route::get('sistema/aprobaciones-compensacion', \App\Livewire\Sistema\AprobacionesCompensacion::class)
        ->middleware(['auth'])
        ->name('sistema.aprobaciones-compensacion');

    // // Nueva ruta: Aprobacion Pago
    Route::get('sistema/aprobacion-pago', \App\Livewire\Sistema\AprobacionPago::class)
        ->middleware(['auth'])
        ->name('sistema.aprobacion-pago');

    // Rutas del sistema de workflow
    Route::prefix('workflow')->name('workflow.')->group(function () {
        Route::get('/demo', [App\Http\Controllers\WorkflowController::class, 'demo'])->name('demo');
        Route::get('/flujos', [App\Http\Controllers\WorkflowController::class, 'obtenerFlujos'])->name('flujos');
        Route::get('/flujo/{flujoId}', [App\Http\Controllers\WorkflowController::class, 'obtenerFlujo'])->name('flujo');
        Route::get('/solicitud/{solicitudId}/transiciones', [App\Http\Controllers\WorkflowController::class, 'obtenerTransicionesDisponibles'])->name('transiciones');
        Route::post('/solicitud/{solicitudId}/transicion', [App\Http\Controllers\WorkflowController::class, 'ejecutarTransicion'])->name('ejecutar-transicion');
        Route::get('/solicitud/{solicitudId}/historial', [App\Http\Controllers\WorkflowController::class, 'obtenerHistorial'])->name('historial');
        Route::post('/crear-solicitud-prueba', [App\Http\Controllers\WorkflowController::class, 'crearSolicitudPrueba'])->name('crear-solicitud-prueba');
    });
});



// Route::get('sistema/ingreso_he', [SistemaController::class, 'menu'])
//     ->middleware(['auth', 'verified'])
//     ->name('ingreso_he');


// Route::get('sistema', [SistemaController::class, 'menu'])
//     ->middleware(['auth', 'verified'])
//     ->name('sistema');

Route::view('sistema/profile', 'sistema.profile')
    ->middleware(['auth', 'verified'])
    ->name('sistema.profile');



Route::middleware(['auth', 'verified'])->group(function () {
    // Route::get('solicitud-hes/create', [SolicitudHeController::class, 'create'])->name('solicitud-hes.create');
    // Route::post('solicitud-hes', [SolicitudHeController::class, 'store'])->name('solicitud-hes.store');
});

require __DIR__.'/auth.php';
