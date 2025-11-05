<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <style>
        /* Fix temporal: forzar columnas en pantallas >= md si Tailwind no se está aplicando */
        @media (min-width: 768px) {
            .force-grid-md { display: grid !important; grid-template-columns: repeat(3, minmax(0, 1fr)) !important; gap: 1.5rem !important; }
        }
    </style>
    <!-- Encabezado -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Solicitud de Compensación</h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            Solicite tiempo libre utilizando su bolsón de horas extras
        </p>
    </div>

    <!-- Mensajes de estado -->
    @if (session()->has('mensaje'))
        <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('mensaje') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="mb-4 bg-blue-50 border-l-4 border-blue-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">{{ session('info') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">{{ session('warning') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Panel de saldo disponible -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mb-8 force-grid-md">
        <!-- Saldo disponible -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 {{ $puedeCompensar ? 'border-green-400' : 'border-red-400' }}">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 {{ $puedeCompensar ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Tiempo Disponible</h3>
                    <p class="text-2xl font-bold font-mono {{ $puedeCompensar ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($saldoDisponible) }} min
                    </p>
                    <p class="text-sm text-gray-500 font-mono">
                        ({{ number_format($saldoDisponible / 60, 1) }} horas)
                    </p>
                </div>
            </div>
        </div>

        <!-- Tiempo pendiente -->
        @if($resumenBolson)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-yellow-400">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Por Aprobar</h3>
                    <p class="text-2xl font-bold font-mono text-yellow-600">
                        {{ number_format($resumenBolson['total_pendiente'] ?? 0) }} min
                    </p>
                    <p class="text-sm text-gray-500 font-mono">
                        ({{ number_format(($resumenBolson['total_pendiente'] ?? 0) / 60, 1) }} horas)
                    </p>
                </div>
            </div>
        </div>

        <!-- Total proyectado -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-blue-400">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Total Proyectado</h3>
                    <p class="text-2xl font-bold font-mono text-blue-600">
                        {{ number_format($resumenBolson['total_general'] ?? 0) }} min
                    </p>
                    <p class="text-sm text-gray-500 font-mono">
                        ({{ number_format(($resumenBolson['total_general'] ?? 0) / 60, 1) }} horas)
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Formulario de solicitud -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Nueva Solicitud de Compensación</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                @if($puedeCompensar)
                    Complete los datos para solicitar tiempo libre
                @else
                    <span class="text-red-600">No tiene tiempo disponible para compensar</span>
                @endif
            </p>
        </div>

        <div class="p-6">
            @if($puedeCompensar)
            <form wire:submit.prevent="save" class="space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-6">
                    <!-- Fecha de compensación -->
                    <div>
                        <label for="fecha_solicitud" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Fecha de Compensación
                        </label>
                        <input
                            type="date"
                            id="fecha_solicitud"
                            wire:model="fecha_solicitud"
                            min="{{ date('Y-m-d') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            required
                        >
                        @error('fecha_solicitud') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Observaciones -->
                    <div>
                        <label for="observaciones" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Observaciones (opcional)
                        </label>
                        <textarea
                            id="observaciones"
                            wire:model="observaciones"
                            rows="3"
                            maxlength="500"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            placeholder="Motivo de la compensación..."
                        ></textarea>
                        @error('observaciones') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <!-- Hora inicial -->
                    <div>
                        <label for="hrs_inicial" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Hora Inicial
                        </label>
                        <input
                            type="time"
                            id="hrs_inicial"
                            wire:model.live.debounce.500ms="hrs_inicial"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            required
                        >
                        @error('hrs_inicial') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Hora final -->
                    <div>
                        <label for="hrs_final" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Hora Final
                        </label>
                        <input
                            type="time"
                            id="hrs_final"
                            wire:model.live.debounce.500ms="hrs_final"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                            required
                        >
                        @error('hrs_final') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Minutos calculados -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Minutos Solicitados
                        </label>
                        <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-600 rounded-md">
                            <span class="text-lg font-semibold {{ $minutos_solicitados > $saldoDisponible ? 'text-red-600' : 'text-green-600' }}">
                                {{ $minutos_solicitados ?: '0' }} min
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400 ml-2">
                                ({{ $minutos_solicitados ? number_format($minutos_solicitados/60, 1) : '0' }} hrs)
                            </span>
                            @if($minutos_solicitados > $saldoDisponible && $minutos_solicitados > 0)
                                <div class="text-red-500 text-sm mt-1">
                                    ⚠️ Excede saldo disponible
                                </div>
                            @endif
                        </div>
                        @error('minutos_solicitados') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex space-x-4">
                    <button
                        type="submit"
                        @disabled(!$puedeCompensar || $minutos_solicitados <= 0 || $minutos_solicitados > $saldoDisponible)
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Solicitar Compensación
                    </button>

                    <button
                        type="button"
                        wire:click="simularDescuento"
                        @disabled($minutos_solicitados <= 0)
                        class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        Simular Descuento
                    </button>
                </div>
            </form>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Sin tiempo disponible</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Necesita tener horas extras aprobadas para poder solicitar compensaciones
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('sistema.ingreso-he') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Ingresar Horas Extras
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Tabla de solicitudes -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Mis Solicitudes de Compensación
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            ID
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Fecha Solicitud
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Horario
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Minutos
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Estado
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Observaciones
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Fecha Aprobación
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($solicitudes as $solicitud)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                #{{ $solicitud->id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                {{ $solicitud->fecha_solicitud ? \Carbon\Carbon::parse($solicitud->fecha_solicitud)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                @php
                                    try {
                                        $horaInicial = $solicitud->hrs_inicial ? \Carbon\Carbon::createFromTimeString($solicitud->hrs_inicial)->format('H:i') : '';
                                        $horaFinal = $solicitud->hrs_final ? \Carbon\Carbon::createFromTimeString($solicitud->hrs_final)->format('H:i') : '';
                                        echo "{$horaInicial} - {$horaFinal}";
                                    } catch (\Exception $e) {
                                        echo $solicitud->hrs_inicial . ' - ' . $solicitud->hrs_final . ' (formato inválido)';
                                    }
                                @endphp
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($solicitud->minutos_solicitados ?? 0) }} min
                                </div>
                                <div class="text-xs text-gray-500">
                                    ({{ number_format(($solicitud->minutos_solicitados ?? 0) / 60, 1) }} hrs)
                                </div>
                                @if($solicitud->minutos_aprobados && $solicitud->minutos_aprobados != $solicitud->minutos_solicitados)
                                    <div class="text-xs text-blue-600">
                                        Aprobado: {{ number_format($solicitud->minutos_aprobados) }} min
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($solicitud->id_estado == 1)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                        {{ $solicitud->estado?->gls_estado ?? 'Pendiente' }}
                                    </span>
                                @elseif($solicitud->id_estado == 2)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        {{ $solicitud->estado?->gls_estado ?? 'Aprobado' }}
                                    </span>
                                @elseif($solicitud->id_estado == 3)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                        {{ $solicitud->estado?->gls_estado ?? 'Rechazado' }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300">
                                        {{ $solicitud->estado?->gls_estado ?? 'Otro' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                                <div class="max-w-xs truncate" title="{{ $solicitud->observaciones }}">
                                    {{ $solicitud->observaciones ?: '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                @if($solicitud->fecha_aprobacion)
                                    {{ \Carbon\Carbon::parse($solicitud->fecha_aprobacion)->format('d/m/Y H:i') }}
                                    <div class="text-xs text-gray-500">
                                        Por: {{ $solicitud->aprobado_por ?? '-' }}
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay solicitudes</h3>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    Comience creando su primera solicitud de compensación
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
