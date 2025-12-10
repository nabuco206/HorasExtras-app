<section class="w-full min-h-screen">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800 mb-4">🔍 Todas las Compensaciones</h1>
        <p class="text-sm text-gray-600 dark:text-gray-400">Listado de todas las solicitudes de compensación</p>
    </div>

    <!-- Filtros -->
    <div class="mb-6 bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Filtros</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <!-- Filtro por usuario -->
            <div>
                <label for="filtro_usuario" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Usuario
                </label>
                <input
                    type="text"
                    id="filtro_usuario"
                    wire:model.defer="filtro_usuario"
                    placeholder="Ingrese usuario"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
            </div>

            <!-- Filtro por fecha inicial -->
            <div>
                <label for="filtro_fecha_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Fecha Inicio
                </label>
                <input
                    type="date"
                    id="filtro_fecha_inicio"
                    wire:model.defer="filtro_fecha_inicio"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
            </div>

            <!-- Filtro por fecha final -->
            <div>
                <label for="filtro_fecha_fin" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Fecha Fin
                </label>
                <input
                    type="date"
                    id="filtro_fecha_fin"
                    wire:model.defer="filtro_fecha_fin"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
            </div>

            <!-- Botones -->
            <div class="flex items-end space-x-2">
                <button
                    type="button"
                    wire:click="aplicarFiltros"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Filtrar
                </button>
                <button
                    type="button"
                    wire:click="limpiarFiltros"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600"
                >
                    Limpiar Filtros
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla de solicitudes -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                Solicitudes de Compensación
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fiscalía</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha Solicitud</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Minutos</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($solicitudes as $solicitud)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">#{{ $solicitud->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $solicitud->username ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $solicitud->fiscalias->gls_fiscalia ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $solicitud->fecha_solicitud ? $solicitud->fecha_solicitud->format('d/m/Y') : '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{$solicitud->id_estado}}-{{ $solicitud->estado->descripcion ?? 'Estado desconocido' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ number_format($solicitud->minutos_solicitados ?? 0) }} min</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                <div class="flex space-x-2">
                                    <!-- Mostrar el botón "Rechazar" solo si el estado es 9 -->
                                    @if ($solicitud->id_estado == 9)
                                        <button
                                            wire:click="rechazarSolicitud({{ $solicitud->id }})"
                                            class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                        >
                                            Rechazar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                No hay solicitudes disponibles.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
