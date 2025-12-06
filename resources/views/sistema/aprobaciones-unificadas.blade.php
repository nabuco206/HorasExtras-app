@php
    $tipo = request()->query('tipo', 1);
    $rol = request()->query('rol', null);
    $estado = request()->query('estado', 1);
    $titulo = request()->query('titulo', null);

    // Log::info('****-**Valores recibidos en Blade', [
    //     'tipo' => $tipo,
    //     'rol' => $rol,
    //     'estado' => $estado,
    //     'titulo' => $titulo,
    // ]);

    // Log::info('Valor de rol en Blade', ['rol' => $rol]);

@endphp

<x-layouts.app>
    <title>{{ $titulo }}</title>
     @livewire('sistema.aprobaciones-unificadas', ['tipo' => $tipo, 'rol' => $rol, 'titulo' => $titulo, 'estado' => $estado])

    @php
        // Log::info('Renderizando sistema.aprobaciones-unificadas con parámetros', [
        //     'tipo' => $tipo,
        //     'rol' => $rol,
        //     'estado' => $estado,
        //     'titulo' => $titulo,
        // ]);
    @endphp
</x-layouts.app>
