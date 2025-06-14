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

Route::get('sistema', [SistemaController::class, 'menu'])
    ->middleware(['auth', 'verified'])
    ->name('sistema');    

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('solicitud-hes/create', [SolicitudHeController::class, 'create'])->name('solicitud-hes.create');
    Route::post('solicitud-hes', [SolicitudHeController::class, 'store'])->name('solicitud-hes.store');
});

require __DIR__.'/auth.php';
