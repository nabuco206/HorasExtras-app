<x-layouts.app :title="__('Dashboard')">
    <div x-data="{
        notifications: [
            { id: 1, show: true, color: 'green', icon: 'check', message: 'Alerta al Dashboard CRM!', detail: 'A new software version is available for download.' },
            { id: 2, show: true, color: 'blue', icon: 'info', message: 'Recordatorio', detail: 'No olvides revisar tus solicitudes pendientes.' }
        ],
        showAll() { this.notifications.forEach(n => n.show = true) }
    }">
        <!-- Notificaciones -->
        <template x-for="(n, i) in notifications" :key="n.id">
            <div
                x-show="n.show"
                x-init="setTimeout(() => n.show = false, 10000)"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 translate-y-0"
                x-transition:leave-end="opacity-0 translate-y-2"
                :style="`top: ${24 + i * 80}px; right: 24px;`"
                class="fixed z-50 w-full max-w-xs"
                style="pointer-events: auto;"
                x-cloak
            >
                <div :class="`bg-${n.color}-100 dark:bg-${n.color}-900 border border-${n.color}-200 dark:border-${n.color}-700 shadow-lg rounded-lg flex items-center px-4 py-3 mb-2`">
                    <div class="flex items-center">
                        <template x-if="n.icon === 'check'">
                            <svg class="h-6 w-6 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <template x-if="n.icon === 'info'">
                            <svg class="h-6 w-6 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
                            </svg>
                        </template>
                        <span :class="`text-${n.color}-800 dark:text-${n.color}-200 font-medium`" x-text="n.message"></span>
                    </div>
                    <div class="mb-2 text-sm font-normal" x-text="n.detail"></div>
                    <button @click="n.show = false" class="ml-auto text-gray-500 hover:text-red-600 font-bold text-xl" title="Cerrar">&times;</button>
                </div>
            </div>
        </template>

        <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    {{ __('Dashboard') }}
                </h1>
                <div class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-2">
                    <!-- Campana de notificaciones antes de Bienvenido -->
                    <button
                        @click="showAll()"
                        class="focus:outline-none"
                        title="Mostrar notificaciones"
                    >
                        <svg class="w-6 h-6 text-yellow-500 hover:text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>
                    {{ __('Bienvenido, ') . Auth::user()->name }}
                </div>
            </div>

            <div class="grid auto-rows-min gap-6 md:grid-cols-5">
                <!-- Saldo Bolsón de Tiempo -->
                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-xl border border-emerald-200 dark:border-emerald-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-emerald-900 dark:text-emerald-100">{{ __('Bolsón de Tiempo') }}</h3>
                        <div class="bg-emerald-200 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-200 px-2 py-1 rounded-full text-sm font-medium">
                            {{ $minutosDisponibles }} min
                        </div>
                    </div>
                    <p class="text-emerald-700 dark:text-emerald-300 text-sm mb-3">{{ __('Tiempo disponible para compensación') }}</p>

                    <!-- Mostrar minutos pendientes si existen -->
                    @if($minutosPendientes > 0)
                        <div class="mb-3 flex items-center justify-between bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 border border-yellow-200 dark:border-yellow-800">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm text-yellow-800 dark:text-yellow-200 font-medium">{{ __('Por aprobar') }}</span>
                            </div>
                            <span class="bg-yellow-200 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded-full text-xs font-medium">
                                {{ $minutosPendientes }} min
                            </span>
                        </div>
                    @endif

                    @if($bolsonesProximosVencer > 0)
                        <div class="mt-3 flex items-center text-amber-600 dark:text-amber-400">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-9 1.938A9.953 9.953 0 013 12c0-5.523 4.477-10 10-10s10 4.477 10 10a9.953 9.953 0 01-2.938 7.062M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-xs">{{ $bolsonesProximosVencer }} próximo(s) a vencer</span>
                        </div>
                    @endif
                </div>

                <!-- Minutos por Aprobar -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-yellow-900/20 dark:to-yellow-800/20 rounded-xl border border-yellow-200 dark:border-yellow-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-yellow-900 dark:text-yellow-100">{{ __('Por Aprobar') }}</h3>
                        <div class="bg-yellow-200 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded-full text-sm font-medium">
                            {{ $minutosPendientes }} min
                        </div>
                    </div>
                    <p class="text-yellow-700 dark:text-yellow-300 text-sm">{{ __('Tiempo pendiente de aprobación') }}</p>
                    @if($minutosPendientes > 0)
                        <div class="mt-3 flex items-center text-yellow-600 dark:text-yellow-400">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-xs">En espera de aprobación del jefe</span>
                        </div>
                    @endif
                </div>

                <!-- Solicitudes Pendientes -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Solicitudes Pendientes') }}</h3>
                        <div class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded-full text-sm font-medium">
                            {{ $solicitudesPendientes }}
                        </div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('Solicitudes de horas extras pendientes de aprobación') }}</p>
                </div>

                <!-- Solicitudes Aprobadas -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Solicitudes Aprobadas') }}</h3>
                        <div class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded-full text-sm font-medium">
                            {{ $solicitudesAprobadas }}
                        </div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('Solicitudes de horas extras aprobadas este mes') }}</p>
                </div>

                <!-- Total Horas Extras -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Total Minutos Extras') }}</h3>
                        <div class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full text-sm font-medium">
                            {{ $totalMinutosMes }} min
                        </div>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('Total de minutos extras acumulados este mes') }}</p>
                </div>
            </div>

            <!-- Detalle del Bolsón de Tiempo -->
            @if(count($detalleBolson) > 0 || count($resumenCompleto['detalle_pendientes']) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Detalle del Bolsón de Tiempo') }}</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-left py-2 text-gray-600 dark:text-gray-400 font-medium">{{ __('Solicitud HE') }}</th>
                                <th class="text-left py-2 text-gray-600 dark:text-gray-400 font-medium">{{ __('Minutos Iniciales') }}</th>
                                <th class="text-left py-2 text-gray-600 dark:text-gray-400 font-medium">{{ __('Disponible') }}</th>
                                <th class="text-left py-2 text-gray-600 dark:text-gray-400 font-medium">{{ __('Vencimiento') }}</th>
                                <th class="text-left py-2 text-gray-600 dark:text-gray-400 font-medium">{{ __('Estado') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Bolsones Pendientes -->
                            @foreach($resumenCompleto['detalle_pendientes'] as $bolson)
                            <tr class="border-b border-gray-100 dark:border-gray-800 bg-yellow-50 dark:bg-yellow-900/10">
                                <td class="py-3 text-gray-900 dark:text-gray-100">
                                    #{{ $bolson['solicitud_he_id'] }}
                                </td>
                                <td class="py-3 text-gray-700 dark:text-gray-300">
                                    {{ $bolson['minutos_pendientes'] }} min
                                </td>
                                <td class="py-3 text-gray-500 dark:text-gray-400 italic">
                                    Pendiente
                                </td>
                                <td class="py-3 text-gray-700 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($bolson['fecha_crea'])->format('d/m/Y') }}
                                </td>
                                <td class="py-3">
                                    <span class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded-full text-xs font-medium">
                                        En Espera
                                    </span>
                                </td>
                            </tr>
                            @endforeach

                            <!-- Bolsones Disponibles -->
                            @foreach($detalleBolson as $bolson)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-3 text-gray-900 dark:text-gray-100">
                                    #{{ $bolson['solicitud_he_id'] }}
                                </td>
                                <td class="py-3 text-gray-700 dark:text-gray-300">
                                    {{ $bolson['minutos_iniciales'] }} min
                                </td>
                                <td class="py-3 text-gray-700 dark:text-gray-300">
                                    {{ $bolson['minutos_disponibles'] }} min
                                </td>
                                <td class="py-3 text-gray-700 dark:text-gray-300">
                                    {{ \Carbon\Carbon::parse($bolson['fecha_vencimiento'])->format('d/m/Y') }}
                                </td>
                                <td class="py-3">
                                    @if($bolson['dias_restantes'] < 0)
                                        <span class="bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 px-2 py-1 rounded-full text-xs font-medium">
                                            Vencido
                                        </span>
                                    @elseif($bolson['dias_restantes'] <= 30)
                                        <span class="bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 px-2 py-1 rounded-full text-xs font-medium">
                                            {{ $bolson['dias_restantes'] }} días
                                        </span>
                                    @else
                                        <span class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded-full text-xs font-medium">
                                            Disponible
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <!-- Acciones Rápidas -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Acciones Rápidas') }}</h3>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <a href="{{ route('sistema.ingreso-he') }}"
                       class="flex items-center justify-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                        <div class="text-center">
                            <div class="text-blue-600 dark:text-blue-400 mb-2">
                                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ __('Nueva HE') }}</span>
                        </div>
                    </a>

                    <a href="{{ route('sistema.ingreso-compensacion') }}"
                       class="flex items-center justify-center p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-100 dark:hover:bg-emerald-900/30 transition-colors">
                        <div class="text-center">
                            <div class="text-emerald-600 dark:text-emerald-400 mb-2">
                                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4m-4 12v-6m0 0H8m4 0h4m-4 0v6M3 21h18M3 10h18"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-emerald-600 dark:text-emerald-400">{{ __('Compensación') }}</span>
                        </div>
                    </a>

                    <a href="{{ route('sistema.ciclo-aprobacion') }}"
                       class="flex items-center justify-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                        <div class="text-center">
                            <div class="text-green-600 dark:text-green-400 mb-2">
                                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ __('Ciclo Aprobación') }}</span>
                        </div>
                    </a>

                    <a href="{{ route('sistema.aprobaciones-masivas') }}"
                       class="flex items-center justify-center p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800 hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors">
                        <div class="text-center">
                            <div class="text-indigo-600 dark:text-indigo-400 mb-2">
                                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">{{ __('Aprobaciones Masivas') }}</span>
                        </div>
                    </a>

                    {{-- Mi Perfil (Settings) temporalmente comentado --}}
                    {{-- <a href="{{ route('settings.profile') }}"
                       class="flex items-center justify-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                        <div class="text-center">
                            <div class="text-purple-600 dark:text-purple-400 mb-2">
                                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-purple-600 dark:text-purple-400">{{ __('Mi Perfil') }}</span>
                        </div>
                    </a> --}}

                    <div class="flex items-center justify-center p-4 bg-gray-50 dark:bg-gray-900/20 rounded-lg border border-gray-200 dark:border-gray-800">
                        <div class="text-center">
                            <div class="text-gray-600 dark:text-gray-400 mb-2">
                                <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('Reportes') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
