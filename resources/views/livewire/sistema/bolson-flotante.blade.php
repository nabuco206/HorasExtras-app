{{-- filepath: resources/views/components/sistema/bolson-flotante.blade.php --}}
@props(['saldoDisponible', 'detalleBolson', 'bolsonesProximosVencer'])

<!-- Cuadro Flotante del Bolsón de Tiempo -->
<div class="fixed top-4 right-4 z-40 w-80" x-data="{ expanded: false }">
    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/90 dark:to-emerald-800/90 backdrop-blur-sm rounded-xl border border-emerald-200 dark:border-emerald-700 shadow-lg">
        <!-- Header del cuadro -->
        <div class="p-4 border-b border-emerald-200 dark:border-emerald-700 cursor-pointer" @click="expanded = !expanded">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-emerald-900 dark:text-emerald-100 text-sm">Bolsón de Tiempo</h4>
                        <p class="text-xs text-emerald-700 dark:text-emerald-300">{{ $saldoDisponible }} min disponibles</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    @if($bolsonesProximosVencer > 0)
                        <div class="w-2 h-2 bg-amber-400 rounded-full animate-pulse" title="{{ $bolsonesProximosVencer }} próximo(s) a vencer"></div>
                    @endif
                    <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400 transition-transform" :class="{'rotate-180': expanded}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Contenido expandible -->
        <div x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 max-h-0" x-transition:enter-end="opacity-100 max-h-96" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 max-h-96" x-transition:leave-end="opacity-0 max-h-0" class="overflow-hidden">
            <div class="p-4 space-y-3">
                <!-- Resumen rápido -->
                <div class="bg-emerald-200/50 dark:bg-emerald-800/50 rounded-lg p-3">
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>
                            <div class="text-emerald-700 dark:text-emerald-300 font-medium">Total Disponible</div>
                            <div class="text-emerald-900 dark:text-emerald-100 font-bold">{{ $saldoDisponible }} min</div>
                        </div>
                        <div>
                            <div class="text-emerald-700 dark:text-emerald-300 font-medium">Bolsones Activos</div>
                            <div class="text-emerald-900 dark:text-emerald-100 font-bold">{{ count($detalleBolson) }}</div>
                        </div>
                    </div>
                </div>

                @if($bolsonesProximosVencer > 0)
                    <div class="bg-amber-100 dark:bg-amber-900/50 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-9 1.938A9.953 9.953 0 013 12c0-5.523 4.477-10 10-10s10 4.477 10 10a9.953 9.953 0 01-2.938 7.062M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-xs text-amber-800 dark:text-amber-200 font-medium">
                                {{ $bolsonesProximosVencer }} bolsón(es) próximo(s) a vencer
                            </span>
                        </div>
                    </div>
                @endif

                <!-- Detalle de bolsones -->
                @if(count($detalleBolson) > 0)
                    <div class="space-y-2">
                        <h5 class="text-xs font-medium text-emerald-800 dark:text-emerald-200">Detalle por bolsón:</h5>
                        <div class="max-h-32 overflow-y-auto space-y-1">
                            @foreach($detalleBolson as $bolson)
                                <div class="bg-white dark:bg-emerald-800/30 rounded-md p-2 text-xs">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium text-emerald-900 dark:text-emerald-100">HE #{{ $bolson['solicitud_he_id'] }}</span>
                                        <span class="text-emerald-700 dark:text-emerald-300">{{ round($bolson['minutos_disponibles'] / 60, 1) }}h</span>
                                    </div>
                                    <div class="flex justify-between items-center mt-1">
                                        <span class="text-emerald-600 dark:text-emerald-400">Vence: {{ \Carbon\Carbon::parse($bolson['fecha_vencimiento'])->format('d/m/Y') }}</span>
                                        @if($bolson['dias_restantes'] <= 30 && $bolson['dias_restantes'] >= 0)
                                            <span class="bg-amber-100 dark:bg-amber-900 text-amber-800 dark:text-amber-200 px-1 py-0.5 rounded text-xs">
                                                {{ $bolson['dias_restantes'] }}d
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-4">
                        <svg class="w-8 h-8 text-emerald-400 dark:text-emerald-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400">No hay bolsones de tiempo disponibles</p>
                    </div>
                @endif

                <!-- Acciones rápidas -->
                <div class="pt-2 border-t border-emerald-200 dark:border-emerald-700">
                    <a href="{{ route('sistema.ingreso-compensacion') }}" class="block w-full text-center bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium py-2 px-3 rounded-lg transition-colors">
                        Solicitar Compensación
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FIN Cuadro Flotante del Bolsón de Tiempo -->
