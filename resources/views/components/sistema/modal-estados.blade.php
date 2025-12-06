@props([
    'estadosSolicitud' => [],
    'modalEstadosVisible' => false,
    'onClose' => null,
])

@if($modalEstadosVisible)
<div class="fixed inset-0 z-50 flex items-center justify-center" style="background: rgba(255,255,255,0.6); backdrop-filter: blur(2px);">
    <div class="relative w-full max-w-2xl p-4">
        <div class="relative bg-white rounded-lg shadow border border-gray-200">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 border-b rounded-t">
                <h3 class="text-lg font-semibold text-gray-900">
                    Estados de la Solicitud
                    @php
                        // Extraer id de solicitud si existe (array u objeto)
                        $first = $estadosSolicitud[0] ?? null;
                        $idSolicitudBadge = null;
                        if ($first) {
                            if (is_array($first)) {
                                $idSolicitudBadge = $first['idSolicitud'] ?? ($first['id_solicitud_he'] ?? null);
                            } elseif (is_object($first)) {
                                $idSolicitudBadge = $first->idSolicitud ?? ($first->id_solicitud_he ?? $first->id ?? null);
                            }
                        }
                    @endphp
                    @if($idSolicitudBadge)
                        <span class="ml-2 text-blue-700">#{{ $idSolicitudBadge }}</span>
                    @endif
                </h3>
                <button @if($onClose) wire:click="$emit('$onClose')" @else wire:click="$set('modalEstadosVisible', false)" @endif type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Cerrar modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-6">

            <ol class="relative border-s border-gray-200 dark:border-gray-700">
            @forelse($estadosSolicitud as $estado)
                @php
                    // Normalizar cada entrada (array o modelo/obj)
                    $gls = null;
                    $username = null;
                    $created = null;
                    $descripcion = null;

                    if (is_array($estado)) {
                        $gls = $estado['gls_estado'] ?? $estado['estado'] ?? $estado['descripcion'] ?? null;
                        $descripcion = $estado['descripcion'] ?? null;
                        $username = $estado['username'] ?? $estado['nombre_usuario'] ?? $estado['user'] ?? $estado['usuario'] ?? null;
                        $created = $estado['created_at'] ?? $estado['fecha'] ?? null;
                    } elseif (is_object($estado)) {
                        $gls = $estado->gls_estado ?? $estado->estado ?? $estado->descripcion ?? null;
                        $descripcion = $estado->descripcion ?? null;
                        // si el objeto tiene relación usuario
                        if (isset($estado->usuario)) {
                            // puede ser objeto user o string
                            if (is_object($estado->usuario)) {
                                $username = $estado->usuario->username ?? $estado->usuario->name ?? null;
                            } else {
                                $username = $estado->usuario;
                            }
                        }
                        $username = $username ?? ($estado->username ?? $estado->nombre_usuario ?? $estado->user ?? null);
                        $created = $estado->created_at ?? $estado->fecha ?? null;
                    }

                    // Normalizar formato de fecha
                    if ($created instanceof \Illuminate\Support\Carbon || $created instanceof \DateTime) {
                        $created = \Carbon\Carbon::parse($created)->format('d/m/Y H:i');
                    }

                    // Priorizar descripcion (tbl_estados.descripcion). Si no existe, usar gls_estado.
                    $descripcion = $descripcion ?? ($estado['descripcion'] ?? null) ?? null;
                    $titulo = $descripcion && $descripcion !== '—' ? $descripcion : ($gls ?? 'Desconocido');

                    $username = $username ?? 'Desconocido';
                    $created = $created ?? '-';
                @endphp

                <li class="mb-10 ms-6">
                    <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">
                        <svg class="w-2.5 h-2.5 text-blue-800 dark:text-blue-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                        </svg>
                    </span>
                    <h3 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $titulo }}</h3>
                    <p class="text-sm text-gray-700 dark:text-gray-300 mt-1">Usuario: {{ $username }}</p>
                    <time class="block mb-2 text-sm font-normal leading-none text-gray-400 dark:text-gray-500">Fecha: {{ $created }}</time>
                </li>
            @empty
                <li>No hay estados registrados.</li>
            @endforelse
            </ol>


            </div>
        </div>
    </div>
</div>
@endif
