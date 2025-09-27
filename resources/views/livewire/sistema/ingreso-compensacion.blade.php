<section class="w-full">

    <x-sistema.layout :heading="__('Solicitud de Compensación de Horas')">       <!-- Formulario -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 mb-8 max-w-6xl">
            @if (session()->has('mensaje'))
                <div 
                    x-data="{ show: true }" 
                    x-init="setTimeout(() => show = false, 10000)" 
                    x-show="show" 
                    class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2 flex justify-between items-center"
                >
                    <span>{{ session('mensaje') }}</span>
                    <button 
                        type="button" 
                        class="ml-2 text-green-900 hover:text-red-600 font-bold" 
                        @click="show = false"
                        title="Cerrar"
                    >
                        &times;
                    </button>
                </div>
            @endif
                <form wire:submit.prevent="save"><!-- <form wire:submit="saveSolicitud" class="w-full space-y-6" enctype="multipart/form-data"> -->
                    <flux:input wire:model="username" type="hidden" required readonly />
                    <div class="flex gap-4 items-end">
                       
                        <flux:input wire:model="fecha" :label="__('Fecha')" type="date" class="flex-1" required  />
                        <flux:input wire:model="hrs_inicial" :label="__('Hora Ingreso')" type="time" class="flex-1" required />
                        <flux:input wire:model="hrs_final" :label="__('Hora Salida')" type="time" class="flex-1" required />
                        
                    </div>

                    <div class="flex items-center justify-center gap-4">
                        <flux:button variant="primary" type="submit" class="px-8">{{ __('Ingresar') }}</flux:button>
                        <x-action-message class="me-3" on="profile-updated">
                            {{ __('Guardado !!!.') }}
                        </x-action-message>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 w-full">
                <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Solicitudes de Compensación de Horas</h3>
                </div>

                <div class="p-6">
                    <div class="w-full overflow-x-auto">
                        {{-- Tabla de solicitudes de compensación --}}

<table class="w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400 min-w-max">
    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
        <tr>
            <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">#</th>
            <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Usuario</th>
            <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Fiscalía</th>
            <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Fecha</th>
            <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Hora Inicial</th>
            <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Hora Final</th>
            <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Total Min.</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
        @forelse ($solicitudes as $solicitud)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                <td class="px-2 py-2 text-center font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ $solicitud->id }}</td>
                <td class="px-2 py-2 text-center whitespace-nowrap">{{ $solicitud->username }}</td>
                <td class="px-2 py-2 text-center whitespace-nowrap">{{ $solicitud->cod_fiscalia }}</td>
                <td class="px-2 py-2 text-center whitespace-nowrap">{{ $solicitud->fecha ? \Carbon\Carbon::parse($solicitud->fecha)->format('d/m/Y') : '-' }}</td>
                <td class="px-2 py-2 text-center whitespace-nowrap">{{ $solicitud->hrs_inicial ?? '-' }}</td>
                <td class="px-2 py-2 text-center whitespace-nowrap">{{ $solicitud->hrs_final ?? '-' }}</td>
                <td class="px-2 py-2 text-center font-semibold whitespace-nowrap">{{ $solicitud->total_min ? number_format($solicitud->total_min, 0) : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                    No hay solicitudes registradas
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
                    </div>
                </div>
            </div>
        </div>
    </x-sistema.layout>
  

</section>