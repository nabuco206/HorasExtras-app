<div class="container mx-auto p-6">
    <style>
        /* Fix temporal: forzar columnas en pantallas >= md si Tailwind no se está aplicando */
        @media (min-width: 768px) {
            .force-grid-md-2 { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 1rem !important; }
            .force-grid-md-3 { display: grid !important; grid-template-columns: repeat(3, minmax(0, 1fr)) !important; gap: 1rem !important; }
            .force-grid-md-4 { display: grid !important; grid-template-columns: repeat(4, minmax(0, 1fr)) !important; gap: 1rem !important; }
            .force-grid-md-5 { display: grid !important; grid-template-columns: repeat(5, minmax(0, 1fr)) !important; gap: 1rem !important; }
        }
    </style>

    {{-- Header con estadísticas --}}
    <div class="mb-6">
        <div class="flex justify-between items-start mb-4">
            <h1 class="text-3xl font-bold text-gray-800">🚀 Aprobaciones Masivas de HE</h1>
            <div class="flex space-x-2">
                <button wire:click="actualizarDatos"
                        class="px-3 py-1 bg-blue-100 text-blue-700 rounded text-sm hover:bg-blue-200 transition-colors"
                        title="Actualizar datos">
                    🔄 Actualizar
                </button>
            </div>
        </div>

        @if($estadisticas)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 mb-6 force-grid-md-5">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 cursor-pointer hover:bg-blue-100 transition-colors"
                 wire:click="$set('filtroEstado', '')" title="Ver todas las solicitudes">
                <div class="text-blue-600 text-sm font-medium">Total Solicitudes</div>
                <div class="text-2xl font-bold font-mono text-blue-700">{{ $estadisticas['total_solicitudes'] }}</div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 cursor-pointer hover:bg-yellow-100 transition-colors"
                 wire:click="$set('filtroEstado', '1')" title="Filtrar solo pendientes">
                <div class="text-yellow-600 text-sm font-medium">⏳ Pendientes</div>
                <div class="text-2xl font-bold font-mono text-yellow-700">{{ $estadisticas['pendientes'] }}</div>
                <div class="text-xs font-mono text-gray-500">{{ number_format($estadisticas['minutos_pendientes_total']) }} min</div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 cursor-pointer hover:bg-green-100 transition-colors"
                 wire:click="$set('filtroEstado', '3')" title="Filtrar solo aprobadas">
                <div class="text-green-600 text-sm font-medium">✅ Aprobadas</div>
                <div class="text-2xl font-bold font-mono text-green-700">{{ $estadisticas['aprobadas'] }}</div>
                <div class="text-xs font-mono text-gray-500">{{ number_format($estadisticas['minutos_aprobados_total']) }} min</div>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 cursor-pointer hover:bg-red-100 transition-colors"
                 wire:click="$set('filtroEstado', '4')" title="Filtrar solo rechazadas">
                <div class="text-red-600 text-sm font-medium">❌ Rechazadas</div>
                <div class="text-2xl font-bold font-mono text-red-700">{{ $estadisticas['rechazadas'] }}</div>
                <div class="text-xs font-mono text-gray-500">{{ number_format($estadisticas['minutos_rechazados_total'] ?? 0) }} min</div>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="text-purple-600 text-sm font-medium">📊 % Aprobación</div>
                <div class="text-2xl font-bold font-mono text-purple-700">{{ $estadisticas['porcentaje_aprobacion'] }}%</div>
                <div class="text-xs font-mono text-gray-500">
                    {{ $estadisticas['aprobadas'] }}/{{ $estadisticas['aprobadas'] + $estadisticas['rechazadas'] }}
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Filtros y controles --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mb-4 force-grid-md-4">
            {{-- Filtro por estado --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                <select wire:model.live="filtroEstado" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Todos los estados</option>
                    <option value="1">⏳ Pendientes (Ingresado)</option>
                    <option value="3">✅ Aprobadas</option>
                    <option value="4">❌ Rechazadas</option>
                </select>
            </div>

            {{-- Filtro de búsqueda --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input type="text" wire:model.live="filtroBusqueda" placeholder="Buscar por usuario..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Acciones masivas --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Aprobar Seleccionadas</label>
                <div class="space-y-2">
                    <button wire:click="aprobarSeleccionados"
                            @if(empty($seleccionados)) disabled @endif
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                        ✅ Aprobar ({{ count($seleccionados) }})
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rechazar Seleccionadas</label>
                <div class="space-y-2">
                    <button wire:click="rechazarSeleccionados"
                            @if(empty($seleccionados)) disabled @endif
                            class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                        ❌ Rechazar ({{ count($seleccionados) }})
                    </button>
                </div>
            </div>
        </div>

        {{-- Botones adicionales --}}
        <div class="border-t pt-4">
            <div class="flex flex-wrap gap-3">


                {{-- <button wire:click="exportarSeleccionados"
                        @if(empty($seleccionados)) disabled @endif
                        class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                    📊 Exportar CSV ({{ count($seleccionados) }})
                </button> --}}

                {{-- <button wire:click="actualizarDatos"
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    🔄 Actualizar Datos
                </button> --}}

                {{-- <button wire:click="filtrarMinutosAltos(480)"
                        class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700">
                    ⏰ Filtrar +8hrs
                </button> --}}
            </div>
        </div>
    </div>

    {{-- Tabla de solicitudes --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <input type="checkbox"
                                   @if(count($seleccionados) === count($solicitudes) && count($solicitudes) > 0) checked @endif
                                   wire:click="@if(count($seleccionados) === count($solicitudes)) deseleccionarTodas @else seleccionarTodas @endif"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   title="@if(count($seleccionados) === count($solicitudes)) Deseleccionar todas las solicitudes @else Seleccionar todas las solicitudes visibles @endif">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            📋 ID / Usuario
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            📅 Fecha
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            🕐 Horario
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer"
                            wire:click="filtrarMinutosAltos(480)" title="Clic para filtrar solicitudes de 8+ horas">
                            ⏱️ Minutos
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            📊 Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            💼 Tipo Trabajo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            🏢 Fiscalía
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($solicitudes as $solicitud)
                        <tr class="hover:bg-gray-50 transition-colors
                            @if(in_array($solicitud->id, $seleccionados)) bg-blue-50 @endif
                            @if(($solicitud->total_min ?? 0) >= 480) border-l-4 border-orange-400 @endif">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox"
                                       wire:model.live="seleccionados"
                                       value="{{ $solicitud->id }}"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">#{{ $solicitud->id }}</div>
                                <div class="text-sm text-gray-500">
                                    👤 {{ $solicitud->username }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $solicitud->fecha ? \Carbon\Carbon::parse($solicitud->fecha)->format('d/m/Y') : '-' }}
                                <div class="text-xs text-gray-500">
                                    {{ $solicitud->created_at ? $solicitud->created_at->format('H:i') : '' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">
                                    🕐 <span class="font-mono">{{ $solicitud->hrs_inicial }} - {{ $solicitud->hrs_final }}</span>
                                </div>
                                <div class="text-xs text-gray-500">
                                    <span class="font-mono">{{ $solicitud->hrs_inicial && $solicitud->hrs_final ?
                                       number_format((strtotime($solicitud->hrs_final) - strtotime($solicitud->hrs_inicial)) / 3600, 1) . 'h' : '' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium font-mono text-gray-900
                                    @if(($solicitud->total_min ?? 0) >= 480) text-orange-600 font-bold @endif">
                                    ⏱️ {{ number_format($solicitud->total_min ?? 0) }} min
                                </div>
                                <div class="text-sm font-mono text-gray-500">
                                    ({{ number_format(($solicitud->total_min ?? 0) / 60, 1) }} hrs)
                                </div>
                                @if(($solicitud->total_min ?? 0) >= 480)
                                    <div class="text-xs text-orange-600 font-medium">🔥 +8 horas</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($solicitud->id_estado == 1)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⏳ Pendiente
                                    </span>
                                @elseif($solicitud->id_estado == 3)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✅ Aprobada
                                    </span>
                                @elseif($solicitud->id_estado == 4)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ❌ Rechazada
                                    </span>
                                @elseif($solicitud->id_estado == 5)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        🔄 Comp. Solicitada
                                    </span>
                                @elseif($solicitud->id_estado == 6)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        🎯 Comp. Aprobada
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $solicitud->estado?->gls_estado ?? 'Estado ' . $solicitud->id_estado }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                💼 {{ $solicitud->tipoTrabajo?->gls_tipo_trabajo ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                🏢 {{ $solicitud->fiscalia?->nombre ?? $solicitud->cod_fiscalia ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No hay solicitudes</h3>
                                    <p class="text-gray-500">
                                        @if($filtroEstado || $filtroBusqueda)
                                            No se encontraron solicitudes que coincidan con los filtros aplicados.
                                        @else
                                            No hay solicitudes de HE registradas en el sistema.
                                        @endif
                                    </p>
                                    @if($filtroEstado || $filtroBusqueda)
                                        <button wire:click="$set('filtroEstado', ''); $set('filtroBusqueda', '')"
                                                class="mt-3 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                            Limpiar Filtros
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal de resultados --}}
    @if($mostrarResultados && $ultimaOperacion)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-[100]" wire:click="cerrarResultados">
        <div class="relative top-4 bottom-4 mx-auto my-8 p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white"
             wire:click.stop>
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-xl font-bold text-gray-900">
                        📋 Resultado de Operación Masiva
                    </h3>
                    <button wire:click="cerrarResultados"
                            class="text-gray-400 hover:text-gray-600 p-2 rounded-full hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <!-- Resumen Principal -->
                    <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-6">
                        <h4 class="text-lg font-semibold text-green-800 mb-3">✅ Procesamiento Completado</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 force-grid-md-4">
                            <div class="text-center">
                                <div class="text-2xl font-bold font-mono text-green-700">{{ $ultimaOperacion['procesadas'] ?? 0 }}</div>
                                <div class="text-sm text-green-600">Procesadas</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold font-mono text-blue-700">{{ count($ultimaOperacion['bolsones_creados'] ?? []) }}</div>
                                <div class="text-sm text-blue-600">Bolsones Creados</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold font-mono text-purple-700">
                                    {{ number_format(array_sum(array_column($ultimaOperacion['bolsones_creados'] ?? [], 'minutos'))) }}
                                </div>
                                <div class="text-sm text-purple-600">Minutos Totales</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold font-mono text-orange-700">
                                    {{ number_format(array_sum(array_column($ultimaOperacion['bolsones_creados'] ?? [], 'minutos')) / 60, 1) }}
                                </div>
                                <div class="text-sm text-orange-600">Horas Totales</div>
                            </div>
                        </div>
                        @if(!empty($ultimaOperacion['mensaje']))
                            <div class="mt-4 p-3 bg-white rounded border">
                                <p class="text-sm text-gray-700">{{ $ultimaOperacion['mensaje'] }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Detalle de bolsones creados -->
                    @if(!empty($ultimaOperacion['bolsones_creados']))
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-blue-800 mb-3 flex items-center">
                                🎯 Bolsones de Tiempo Creados
                                <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs">
                                    {{ count($ultimaOperacion['bolsones_creados']) }}
                                </span>
                            </h4>
                            <div class="max-h-64 overflow-y-auto">
                                <div class="space-y-2">
                                    @foreach($ultimaOperacion['bolsones_creados'] as $index => $bolson)
                                        <div class="flex justify-between items-center p-3 bg-white rounded border-l-4 border-blue-400">
                                            <div class="flex-1">
                                                <div class="font-medium text-blue-800">
                                                    HE #{{ $bolson['solicitud_id'] }}
                                                </div>
                                                <div class="text-sm text-gray-600">
                                                    👤 {{ $bolson['username'] }}
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-bold font-mono text-blue-700">
                                                    {{ number_format($bolson['minutos']) }} min
                                                </div>
                                                <div class="text-sm font-mono text-gray-500">
                                                    ({{ number_format($bolson['minutos'] / 60, 1) }} hrs)
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Errores si los hay -->
                    @if(!empty($ultimaOperacion['errores']))
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-red-800 mb-3 flex items-center">
                                ⚠️ Errores Encontrados
                                <span class="ml-2 px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs">
                                    {{ count($ultimaOperacion['errores']) }}
                                </span>
                            </h4>
                            <div class="space-y-2">
                                @foreach($ultimaOperacion['errores'] as $error)
                                    <div class="p-3 bg-white rounded border-l-4 border-red-400">
                                        <div class="text-sm text-red-700">
                                            <strong>Solicitud #{{ $error['solicitud_id'] ?? 'N/A' }}:</strong>
                                            {{ $error['error'] }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Estadísticas adicionales si están disponibles -->
                    @if(!empty($ultimaOperacion['estadisticas_actualizadas']))
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-gray-800 mb-2">📊 Estadísticas del Sistema Actualizadas</h4>
                            <div class="text-sm text-gray-600">
                                Los datos mostrados en el dashboard han sido actualizados automáticamente.
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-6 mb-4 flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        Operación completada: {{ now()->format('d/m/Y H:i:s') }}
                    </div>
                    <div class="flex space-x-3">
                        <button wire:click="exportarSeleccionados"
                                class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
                            📊 Exportar Resultados
                        </button>
                        <button wire:click="cerrarResultados"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Mensajes de estado --}}
    @if (session()->has('mensaje'))
        <div class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
            {{ session('mensaje') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
            {{ session('error') }}
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="fixed bottom-4 right-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded z-50">
            {{ session('warning') }}
        </div>
    @endif
</div>
