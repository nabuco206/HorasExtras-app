{{-- <div class="p-6">--}}
<section class="w-full min-h-screen">
    @if($tieneAcceso)
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Monitoreo de Tiempo Disponible</h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Supervise el tiempo acumulado en bolsón de todas las fiscalías
                    </p>
                </div>

                <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 rounded-lg text-sm font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    {{ count($personas) }} {{ count($personas) === 1 ? 'Usuario' : 'Usuarios' }}
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="fiscalia" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Fiscalia
                        </label>
                        <select wire:model.live="fiscaliaSeleccionada" id="fiscalia"
                                class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-zinc-800 dark:text-white">
                            <option value="">Todas las fiscalías</option>
                            @foreach($fiscalias as $fiscalia)
                                <option value="{{ $fiscalia->cod_fiscalia }}">{{ $fiscalia->gls_fiscalia }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="tipo_compensacion" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            Tipo de Compensación
                        </label>
                        <select wire:model.live="tipoCompensacionSeleccionada" id="tipo_compensacion"
                                class="w-full px-3 py-2 border border-zinc-300 dark:border-zinc-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-zinc-800 dark:text-white">
                            <option value="">Todos los tipos</option>
                            @foreach($tiposCompensacion as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->gls_tipo_compensacion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tabla de usuarios -->
            @if(count($personas) > 0)
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                                        ID
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                                        Nombre Completo
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                                        Usuario
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                                        Fiscalia
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                                        Escalafón
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                                        Tiempo disponible
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider">
                                        Estado
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach($personas as $persona)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-900 dark:text-zinc-100">
                                            {{ $persona->id }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                            {{ $persona->nombre }} {{ $persona->apellido }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ $persona->username }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ $persona->fiscalia->gls_fiscalia ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ $persona->escalafon->gls_escalafon ?? 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ isset($persona->tiempo_disponible) ? $persona->tiempo_disponible . ' min / ' . number_format($persona->tiempo_disponible / 60, 2) . ' hrs' : '0 min / 0.00 hrs' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($persona->flag_activo)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                                    Activo
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-700 dark:text-zinc-300">
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-zinc-900 rounded-lg shadow p-12">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-zinc-400 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">
                            No hay usuarios con los filtros aplicados
                        </h3>
                        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                            Intente cambiar los filtros de fiscalía o tipo de compensación.
                        </p>
                    </div>
                </div>
            @endif
        </div>
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
