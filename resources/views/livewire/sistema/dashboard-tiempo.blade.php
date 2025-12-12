{{-- <div class="p-6">--}}
<section class="w-full min-h-screen">
    @if($tieneAcceso)
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Dashboard de Tiempo Disponible</h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Resumen de horas extras por fiscalía
                    </p>
                </div>
            </div>

            <!-- Grid de fiscalías -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($resumenFiscalias as $codFiscalia => $datos)
                    @php
                        $tipo1 = $datos->where('id_tipo_compensacion', 1)->first();
                        $tipo2 = $datos->where('id_tipo_compensacion', 2)->first();
                        $totalTipo1 = $tipo1 ? $tipo1->total_minutos : 0;
                        $totalTipo2 = $tipo2 ? $tipo2->total_minutos : 0;
                        $nombreFiscalia = $tipo1 ? $tipo1->gls_fiscalia : ($tipo2 ? $tipo2->gls_fiscalia : 'N/A');
                    @endphp
                    <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6 cursor-pointer hover:shadow-lg transition-shadow"
                         wire:click="mostrarDetalle({{ $codFiscalia }})">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $nombreFiscalia }}</h3>
                            <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Compensación en Hrs</span>
                                <span class="text-lg font-bold text-blue-600">{{ number_format($totalTipo1 / 60, 2) }} hrs</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Dinero</span>
                                <span class="text-lg font-bold text-green-600">{{ number_format($totalTipo2 / 60, 2) }} hrs</span>
                            </div>
                            <div class="pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-zinc-900 dark:text-white">Total</span>
                                    <span class="text-xl font-bold text-zinc-900 dark:text-white">{{ number_format(($totalTipo1 + $totalTipo2) / 60, 2) }} hrs</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(count($resumenFiscalias) == 0)
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-12">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">
                            No hay datos disponibles
                        </h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            No se encontraron horas extras acumuladas.
                        </p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Modal de detalle -->
        @if($detalleFiscalia)
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-hidden">
                    <div class="flex items-center justify-between p-6 border-b border-zinc-200 dark:border-zinc-700">
                        <h2 class="text-xl font-semibold text-zinc-900 dark:text-white">
                            Detalle de {{ $resumenFiscalias[$detalleFiscalia]->first()->gls_fiscalia ?? 'Fiscalía' }}
                        </h2>
                        <button wire:click="cerrarDetalle" class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="p-6 overflow-y-auto max-h-[70vh]">
                        @if(count($detalleUsuarios) > 0)
                            <div class="overflow-x-auto">
                                <table class="w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                                                Usuario
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                                                Nombre
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                                                Compensación (hrs)
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                                                Dinero (hrs)
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                                                Total (hrs)
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                        @foreach($detalleUsuarios as $username => $datosUsuario)
                                            @php
                                                $tipo1User = $datosUsuario->where('id_tipo_compensacion', 1)->first();
                                                $tipo2User = $datosUsuario->where('id_tipo_compensacion', 2)->first();
                                                $totalTipo1User = $tipo1User ? $tipo1User->total_minutos : 0;
                                                $totalTipo2User = $tipo2User ? $tipo2User->total_minutos : 0;
                                                $nombre = $tipo1User ? $tipo1User->nombre . ' ' . $tipo1User->apellido : ($tipo2User ? $tipo2User->nombre . ' ' . $tipo2User->apellido : 'N/A');
                                            @endphp
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                                    {{ $username }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ $nombre }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                                    {{ number_format($totalTipo1User / 60, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                                    {{ number_format($totalTipo2User / 60, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                                    {{ number_format(($totalTipo1User + $totalTipo2User) / 60, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-center text-zinc-600 dark:text-zinc-400">No hay usuarios con tiempo disponible en esta fiscalía.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    @else
        <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-12">
            <div class="text-center">
                <svg class="mx-auto h-12 w-12 text-red-400 dark:text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">
                    Acceso denegado
                </h3>
                <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                    No tienes permisos para acceder a esta página.
                </p>
            </div>
        </div>
    @endif
</section>
