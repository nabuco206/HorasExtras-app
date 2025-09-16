@props([
    'estadosSolicitud' => [],
    'modalEstadosVisible' => false,
    'onClose' => null,
])

@if($modalEstadosVisible)
<div class="fixed inset-0 z-50 flex items-center justify-center" style="background: rgba(255,255,255,0.6); backdrop-filter: blur(2px);">
    <div class="relative w-full max-w-2xl p-4">
        <div class="relative bg-white rounded-lg shadow border border-gray-200">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 border-b rounded-t">
                <h3 class="text-lg font-semibold text-gray-900">
                    Estados de la Solicitud
                    @if(!empty($estadosSolicitud))
                        <span class="ml-2 text-blue-700">#{{ $estadosSolicitud[0]['idSolicitud'] ?? '' }}</span>
                    @endif
                </h3>
                <button @if($onClose) wire:click="$emit('$onClose')" @else wire:click="$set('modalEstadosVisible', false)" @endif type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                    <svg class="w-3 h-3" aria-hidden="true" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Cerrar modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-6">

            <ol class="relative border-s border-gray-200 dark:border-gray-700">
            @forelse($estadosSolicitud as $estado)
                <li class="mb-10 ms-6">
                    <span class="absolute flex items-center justify-center w-6 h-6 bg-blue-100 rounded-full -start-3 ring-8 ring-white dark:ring-gray-900 dark:bg-blue-900">
                        <svg class="w-2.5 h-2.5 text-blue-800 dark:text-blue-300" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M20 4a2 2 0 0 0-2-2h-2V1a1 1 0 0 0-2 0v1h-3V1a1 1 0 0 0-2 0v1H6V1a1 1 0 0 0-2 0v1H2a2 2 0 0 0-2 2v2h20V4ZM0 18a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8H0v10Zm5-8h10a1 1 0 0 1 0 2H5a1 1 0 0 1 0-2Z"/>
                        </svg>
                    </span>
                    <h3 class="mb-1 text-lg font-semibold text-gray-900 dark:text-white">{{ $estado['gls_estado'] }}</h3>
                    <time class="block mb-2 text-sm font-normal leading-none text-gray-400 dark:text-gray-500">Fecha: {{ $estado['created_at'] }}</time>
                    {{-- <p class="text-base font-normal text-gray-500 dark:text-gray-400">All of the pages and components are first designed in Figma and we keep a parity between the two versions even as we update the project.</p> --}}
                </li>
            @empty
                <li>No hay estados registrados.</li>
            @endforelse
            </ol>


            </div>
        </div>
    </div>
</div>
@endif
