<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('Dashboard') }}</h1>
            <div class="text-sm text-gray-500 dark:text-gray-400">
                {{ __('Bienvenido, ') . Auth::user()->name }}
            </div>
        </div>

        <div class="grid auto-rows-min gap-6 md:grid-cols-3">
            <!-- Solicitudes Pendientes -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Solicitudes Pendientes') }}</h3>
                    <div class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200 px-2 py-1 rounded-full text-sm font-medium">
                        0
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('Solicitudes de horas extras pendientes de aprobación') }}</p>
            </div>

            <!-- Solicitudes Aprobadas -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Solicitudes Aprobadas') }}</h3>
                    <div class="bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 px-2 py-1 rounded-full text-sm font-medium">
                        0
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('Solicitudes de horas extras aprobadas este mes') }}</p>
            </div>

            <!-- Total Horas Extras -->
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ __('Total Horas Extras') }}</h3>
                    <div class="bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full text-sm font-medium">
                        0h
                    </div>
                </div>
                <p class="text-gray-600 dark:text-gray-400 text-sm">{{ __('Total de horas extras acumuladas este mes') }}</p>
            </div>
        </div>

        <!-- Acciones Rápidas -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ __('Acciones Rápidas') }}</h3>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('solicitud-hes.create') }}"
                   class="flex items-center justify-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors">
                    <div class="text-center">
                        <div class="text-blue-600 dark:text-blue-400 mb-2">
                            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-blue-600 dark:text-blue-400">{{ __('Nueva Solicitud') }}</span>
                    </div>
                </a>

                <a
                   class="flex items-center justify-center p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800 hover:bg-green-100 dark:hover:bg-green-900/30 transition-colors">
                    <div class="text-center">
                        <div class="text-green-600 dark:text-green-400 mb-2">
                            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-green-600 dark:text-green-400">{{ __('Ver Sistema') }}</span>
                    </div>
                </a>

                <a href="{{ route('settings.profile') }}"
                   class="flex items-center justify-center p-4 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-colors">
                    <div class="text-center">
                        <div class="text-purple-600 dark:text-purple-400 mb-2">
                            <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-purple-600 dark:text-purple-400">{{ __('Mi Perfil') }}</span>
                    </div>
                </a>

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
</x-layouts.app>
