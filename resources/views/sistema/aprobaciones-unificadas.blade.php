@php
    $tipo = request()->query('tipo', 1);
    $rol = request()->query('rol', null);
    $estado = request()->query('estado', 1);
    $titulo = request()->query('titulo', null);
@endphp

<x-layouts.app>
    <title>{{ $titulo }}</title>
     @livewire('sistema.aprobaciones-unificadas', ['tipo' => $tipo, 'rol' => $rol, 'titulo' => $titulo])
</x-layouts.app>
