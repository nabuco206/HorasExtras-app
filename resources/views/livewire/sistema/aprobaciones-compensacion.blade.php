<div class="container mx-auto p-6">
    {{-- Estilos centralizados en resources/css/app.css --}}

    {{-- Header con estadísticas --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">🔍 Aprobación de Compensaciones</h1>

        @if($estadisticas)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4 mb-6 force-grid-md-5">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="text-blue-600 text-sm font-medium">Total Solicitudes</div>
                <div class="text-2xl font-bold font-mono text-blue-700">{{ $estadisticas['total_solicitudes'] }}</div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="text-yellow-600 text-sm font-medium">⏳ Pendientes</div>
                <div class="text-2xl font-bold font-mono text-yellow-700">{{ $estadisticas['pendientes'] }}</div>
                <div class="text-xs text-gray-500 font-mono">{{ number_format($estadisticas['minutos_pendientes_total']) }} min</div>
            </div>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="text-green-600 text-sm font-medium">✅ Aprobadas</div>
                <div class="text-2xl font-bold font-mono text-green-700">{{ $estadisticas['aprobadas'] }}</div>
                <div class="text-xs text-gray-500 font-mono">{{ number_format($estadisticas['minutos_aprobados_total']) }} min</div>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="text-red-600 text-sm font-medium">❌ Rechazadas</div>
                <div class="text-2xl font-bold font-mono text-red-700">{{ $estadisticas['rechazadas'] }}</div>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="text-purple-600 text-sm font-medium">📊 % Aprobación</div>
                <div class="text-2xl font-bold font-mono text-purple-700">{{ $estadisticas['porcentaje_aprobacion'] }}%</div>
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
                    <option value="8">⏳ Solicitadas (Pendientes)</option>
                    <option value="9">✅ Aprobadas</option>
                    <option value="10">❌ Rechazadas</option>
                </select>
            </div>

            {{-- Filtro de búsqueda --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Buscar</label>
                <input type="text" wire:model.live="filtroBusqueda" placeholder="Buscar por usuario o nombre..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            {{-- Acciones masivas --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Aprobar Seleccion</label>
                <div class="space-y-2">
                    <button wire:click="aprobarSeleccionadas"
                            @if(empty($solicitudesSeleccionadas)) disabled @endif
                            class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                        ✅ Aprobar ({{ count($solicitudesSeleccionadas) }})
                    </button>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rechazar Seleccion</label>
                <div class="space-y-2">
                    <button wire:click="rechazarSeleccionadas"
                        @if(empty($solicitudesSeleccionadas)) disabled @endif
                        class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                        ❌ Rechazar ({{ count($solicitudesSeleccionadas) }})
                    </button>
                </div>
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
                                   @if(count($solicitudesSeleccionadas) === $solicitudes->count() && $solicitudes->count() > 0) checked @endif
                                   wire:click="@if(count($solicitudesSeleccionadas) === $solicitudes->count()) deseleccionarTodas @else seleccionarTodas @endif"
                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                   title="@if(count($solicitudesSeleccionadas) === $solicitudes->count()) Deseleccionar todas las compensaciones @else Seleccionar todas las compensaciones visibles @endif">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Solicitud</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Minutos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Bolsón</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($solicitudes as $solicitud)
                        @php
                            $validacion = $this->validarSolicitud($solicitud->id);
                            $saldoBolson = $this->obtenerSaldoBolson($solicitud->username);
                            $esValida = $validacion['valida'];
                        @endphp
                        <tr class="hover:bg-gray-50 @if(!$esValida && $solicitud->id_estado == 8) bg-red-50 @endif">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($solicitud->id_estado == 8)
                                <input type="checkbox"
                                       wire:model.live="solicitudesSeleccionadas"
                                       value="{{ $solicitud->id }}"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $solicitud->username }}</div>
                                <div class="text-sm text-gray-500">
                                    {{ optional($solicitud->persona)->nombre }}
                                    {{ optional($solicitud->persona)->apellido_paterno }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $solicitud->fecha_solicitud->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium font-mono text-gray-900">
                                    {{ number_format($solicitud->minutos_solicitados) }} min
                                </div>
                                @if($solicitud->minutos_aprobados)
                                <div class="text-sm font-mono text-green-600">
                                    Aprobados: {{ number_format($solicitud->minutos_aprobados) }} min
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($solicitud->id_estado == 8)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        ⏳ Solicitada
                                    </span>
                                @elseif($solicitud->id_estado == 9)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        ✅ Aprobada
                                    </span>
                                @elseif($solicitud->id_estado == 10)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        ❌ Rechazada
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $solicitud->estado->descripcion ?? 'Estado ' . $solicitud->id_estado }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-mono text-gray-900">
                                    {{ number_format($saldoBolson) }} min
                                </div>
                                @if(!$esValida && $solicitud->id_estado == 8)
                                <div class="text-xs text-red-600">
                                    ⚠️ {{ $validacion['mensaje'] }}
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($solicitud->id_estado == 8)
                                <div class="flex space-x-2">
                                    {{-- <button wire:click="abrirModalAprobacion({{ $solicitud->id }})"
                                            class="text-red-600 hover:text-red-900 px-2 py-1 border border-red-600 rounded hover:bg-red-50"
                                            title="Rechazar solicitud (abrir modal)">
                                        ❌ Rechazar
                                    </button> --}}
                                    <button wire:click="abrirModalAprobacion({{ $solicitud->id }})"
                                            class="text-green-600 hover:text-green-900 px-2 py-1 border border-green-600 rounded hover:bg-green-50"
                                            title="Evaluar solicitud">
                                        ✅ Evaluar
                                    </button>
                                </div>
                                @elseif($solicitud->id_estado == 9)
                                    <span class="text-green-600">✅ Aprobada</span>
                                    @if($solicitud->aprobado_por)
                                    <div class="text-xs text-gray-500">
                                        Por: {{ $solicitud->aprobado_por }}
                                    </div>
                                    @endif
                                    @if($solicitud->fecha_aprobacion)
                                    <div class="text-xs text-gray-500">
                                        {{ $solicitud->fecha_aprobacion->format('d/m/Y H:i') }}
                                    </div>
                                    @endif
                                @elseif($solicitud->id_estado == 10)
                                    <span class="text-red-600">❌ Rechazada</span>
                                    @if($solicitud->aprobado_por)
                                    <div class="text-xs text-gray-500">
                                        Por: {{ $solicitud->aprobado_por }}
                                    </div>
                                    @endif
                                    @if($solicitud->fecha_aprobacion)
                                    <div class="text-xs text-gray-500">
                                        {{ $solicitud->fecha_aprobacion->format('d/m/Y H:i') }}
                                    </div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                No se encontraron solicitudes de compensación.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="px-6 py-4 bg-gray-50">
            {{ $solicitudes->links() }}
        </div>
    </div>

    {{-- Modal de aprobación/rechazo --}}
    @if($mostrarModal && $solicitudSeleccionada)
    <div class="modal-overlay" wire:click="cerrarModal">
        <div class="modal-panel" wire:click.stop>
            <div class="modal-header">
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    📋 Aprobar/Rechazar Compensación
                </h3>
            </div>

            <div class="modal-body">
                {{-- Información de la solicitud --}}
                <div class="bg-gray-50 rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4 force-grid-md-2">
                        <div>
                            <strong>Usuario:</strong> {{ $solicitudSeleccionada->username }}<br>
                            <strong>Nombre:</strong>
                            {{ optional($solicitudSeleccionada->persona)->nombre }}
                            {{ optional($solicitudSeleccionada->persona)->apellido_paterno }}<br>
                            <strong>Fecha:</strong> {{ $solicitudSeleccionada->fecha_solicitud->format('d/m/Y') }}
                        </div>
                        <div>
                            <strong>Minutos Solicitados:</strong> <span class="font-mono">{{ number_format($solicitudSeleccionada->minutos_solicitados) }} min</span><br>
                            <strong>Saldo Bolsón:</strong> <span class="font-mono">{{ number_format($this->obtenerSaldoBolson($solicitudSeleccionada->username)) }} min</span><br>
                            @if($solicitudSeleccionada->observaciones)
                            <strong>Observaciones:</strong> {{ $solicitudSeleccionada->observaciones }}
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Formulario de aprobación --}}
                <div class="space-y-4 mb-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Minutos a Aprobar
                        </label>
                        <input type="number"
                               wire:model="minutosAprobados"
                               min="1"
                               max="{{ $solicitudSeleccionada->minutos_solicitados }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-sm text-gray-500 font-mono mt-1">
                            Máximo: {{ number_format($solicitudSeleccionada->minutos_solicitados) }} min
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Observaciones (opcional)
                        </label>
                        <textarea wire:model="observaciones"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </div>
            </div>

            <div class="modal-actions">
                <button wire:click="cerrarModal"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Cancelar
                </button>
                <button wire:click="rechazarSolicitud"
                        class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    ❌ Rechazar
                </button>
                <button wire:click="aprobarSolicitud"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    ✅ Aprobar
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Mensajes de estado --}}
    @if (session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50">
            {{ session('error') }}
        </div>
    @endif
</div>
