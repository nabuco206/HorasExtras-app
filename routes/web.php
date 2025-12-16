<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompensacionController;
use App\Http\Controllers\PagosConcretadosController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::post('/authenticate', [AuthController::class, 'authenticate'])->name('authenticate');

Route::get('/test-login', function () {
    return view('test-login');
})->name('test-login');

// Route::get('dashboard', [DashboardController::class, 'index'])
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');

Route::get('dashboard', \App\Livewire\Sistema\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    // Sistema routes usando Volt
    Volt::route('sistema/ingreso-he', 'sistema.ingreso-he')->name('sistema.ingreso-he');
    Volt::route('sistema/ciclo-aprobacion', 'sistema.ciclo-aprobacion')->name('sistema.ciclo-aprobacion');

    // Sistema routes usando Livewire
    Route::get('sistema/aprobaciones-masivas', \App\Livewire\Sistema\AprobacionesMasivas::class)
        ->name('sistema.aprobaciones-masivas');

    Route::get('sistema/ingreso-compensacion', \App\Livewire\Sistema\IngresoCompensacion::class)
        ->name('sistema.ingreso-compensacion');

    Route::get('sistema/aprobaciones-compensacion', \App\Livewire\Sistema\AprobacionesCompensacion::class)
        ->name('sistema.aprobaciones-compensacion');

    Route::get('sistema/aprobacion-pago', \App\Livewire\Sistema\AprobacionPago::class)
        ->name('sistema.aprobacion-pago');

    Route::get('sistema/aprobaciones-unificadas', function () {
        return view('sistema.aprobaciones-unificadas');
    })->name('sistema.aprobaciones-unificadas');

    // Nueva ruta para Mi Equipo (Líderes)
    Route::get('sistema/mi-equipo', \App\Livewire\Sistema\MiEquipo::class)
        ->name('sistema.mi-equipo');

    // Nueva ruta para Monitoreo de Tiempo (UDP, JUDP, DER)
    Route::get('sistema/monitoreo-tiempo', \App\Livewire\Sistema\MonitoreoTiempo::class)
        ->name('sistema.monitoreo-tiempo');

    // Nueva ruta para Dashboard de Tiempo (UDP, JUDP, DER)
    Route::get('sistema/dashboard-tiempo', \App\Livewire\Sistema\DashboardTiempo::class)
        ->name('sistema.dashboard-tiempo');

    // Calendario JD view
    Route::get('sistema/calendario-jd', \App\Livewire\Sistema\CalendarioCompensaciones::class)
        ->name('sistema.calendario-jd');

    // Vista genérica: solicitudes a pago
    Route::get('sistema/solicitudes-pago', \App\Livewire\Sistema\ListadorSolicitudesPago::class)
        ->name('sistema.solicitudes-pago');

    Route::get('sistema/solicitudes-pago/export', [App\Http\Controllers\SolicitudesPagoExportController::class, 'export'])
        ->name('sistema.solicitudes-pago.export');

    // Calendario JD - eventos JSON
    Route::get('jd/compensaciones/events', [App\Http\Controllers\CompensacionCalendarController::class, 'events'])
        ->name('jd.compensaciones.events');

    // Demo routes
    // Route::get('/demo-ciclo-aprobacion', \App\Livewire\DemoCicloAprobacion::class)
    //     ->name('demo.ciclo-aprobacion');

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

    // Reemplazo / registro de ruta para "Todas las Compensaciones" usando Livewire
    Route::get('sistema/todas-compensaciones', \App\Livewire\Sistema\TodasCompensaciones::class)
        ->name('sistema.todas-compensaciones')
        ->middleware(['auth']);

    // Rutas para Pagos Concretados
    
    Route::get('sistema/pagos-concretados', \App\Livewire\Sistema\PagosConcretados::class)
        ->name('sistema.pagos-concretados')
        ->middleware(['auth']);;

       
    
});

require __DIR__.'/auth.php';
