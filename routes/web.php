<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\SolicitudHeController;
use App\Http\Controllers\SistemaController;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');  

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
    Volt::route('sistema/ingreso-he', 'sistema.ingreso-he')->name('sistema.ingreso-he');
    // Volt::route('sistema/solicitud', 'sistema.ingreso-hesolicitud_create')->name('sistema.solicitud_create');
});



Route::get('sistema/ingreso_he', [SistemaController::class, 'menu'])
    ->middleware(['auth', 'verified'])
    ->name('ingreso_he');


Route::get('sistema', [SistemaController::class, 'menu'])
    ->middleware(['auth', 'verified'])
    ->name('sistema'); 

Route::view('sistema/profile', 'sistema.profile')
    ->middleware(['auth', 'verified'])
    ->name('sistema.profile');      
    
    

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('solicitud-hes/create', [SolicitudHeController::class, 'create'])->name('solicitud-hes.create');
    Route::post('solicitud-hes', [SolicitudHeController::class, 'store'])->name('solicitud-hes.store');
});

require __DIR__.'/auth.php';
