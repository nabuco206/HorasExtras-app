<?php

use App\Models\TblTipoTrabajo;
use App\Models\TblEstado;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public string $username = '';
    public $tipos_trabajo = [];
    public $estados = [];
    public $solicitudes = [];
    public $modalEstadosVisible = false;
    public $estadosSolicitud = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->username = Auth::user()->name;
        $this->tipos_trabajo = TblTipoTrabajo::all();
        $this->estados = TblEstado::all();
        // Cargar todas las solicitudes (puedes filtrar por usuario o estado si lo deseas)
        $this->solicitudes = \App\Models\TblSolicitudHe::orderByDesc('id')->get();
    }

    /**
     * Muestra el historial de estados de una solicitud usando el Service
     */
    public function verEstados($idSolicitud)
    {
        $service = app(\App\Services\SolicitudHeService::class);
        $this->estadosSolicitud = $service->obtenerEstados($idSolicitud);
        $this->modalEstadosVisible = true;
    }

}; ?>

<section class="w-full">

    <x-sistema.layout :heading="__('Ciclo de Aprobación')">

        <!-- SOLO LA TABLA -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 w-full">
            <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Listado de Solicitudes</h3>
            </div>

            <div class="p-6">
                <div class="w-full overflow-x-auto">
                    <table class="w-full text-xs text-left rtl:text-right text-gray-500 dark:text-gray-400 min-w-max">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400 border-b border-gray-200 dark:border-gray-600">
                            <tr>
                                <th class="px-2 py-2 text-center whitespace-nowrap"></th> <!-- Solo la lupa -->
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">#</th>
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Tipo Trabajo</th>
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Fecha</th>
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Hora Inicial</th>
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Hora Final</th>
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Estado</th>
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Tipo Solicitud</th>
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Hora Inicio</th>
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Hora Fin</th>
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Compensación</th>
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Min. Reales</th>
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Min. 25%</th>
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Min. 50%</th>
                                <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Total Min.</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @forelse ($solicitudes as $solicitud)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-2 py-2 text-center">
                                        <button wire:click="verEstados({{ $solicitud->id }})" type="button" title="Ver estados">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600 hover:text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 104.5 4.5a7.5 7.5 0 0012.15 12.15z" />
                                            </svg>
                                        </button>
                                    </td>
                                    <td class="px-2 py-2 text-center font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ $solicitud->id }}</td>
                                    <td class="px-2 py-2 text-center whitespace-nowrap">
                                        @php
                                            $tipo = $tipos_trabajo->firstWhere('id', $solicitud->id_tipo_trabajo);
                                        @endphp
                                        <span class="max-w-24 block truncate" title="{{ $tipo ? $tipo->gls_tipo_trabajo : '-' }}">
                                            {{ $tipo ? $tipo->gls_tipo_trabajo : '-' }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-2 text-center whitespace-nowrap">{{ $solicitud->fecha ? \Carbon\Carbon::parse($solicitud->fecha)->format('d/m/Y') : '-' }}</td>
                                    <td class="px-2 py-2 text-center whitespace-nowrap">{{ $solicitud->hrs_inicial ?? '-' }}</td>
                                    <td class="px-2 py-2 text-center whitespace-nowrap">{{ $solicitud->hrs_final ?? '-' }}</td>
                                    <td class="px-2 py-2 text-center whitespace-nowrap">
                                        @php
                                            $estado = $estados->firstWhere('id', $solicitud->id_estado);
                                        @endphp
                                        @if($estado)
                                            @if($estado->id == 1)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                                    {{ $estado->gls_estado }}
                                                </span>
                                            @elseif($estado->id == 2)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                    {{ $estado->gls_estado }}
                                                </span>
                                            @elseif($estado->id == 3)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                                    {{ $estado->gls_estado }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300">
                                                    {{ $estado->gls_estado }}
                                                </span>
                                            @endif
                                        @else
                                            <span>{{ $solicitud->id_estado ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 text-center whitespace-nowrap">
                                        <span class="max-w-24 block truncate" title="{{ $solicitud->tipo_solicitud ?? '-' }}">{{ $solicitud->tipo_solicitud ?? '-' }}</span>
                                    </td>
                                    <td class="px-2 py-2 text-center whitespace-nowrap">{{ $solicitud->hrs_inicio ?? '-' }}</td>
                                    <td class="px-2 py-2 text-center whitespace-nowrap">{{ $solicitud->hrs_fin ?? '-' }}</td>
                                    <td class="px-2 py-2 text-center whitespace-nowrap">
                                        @if($solicitud->id_tipoCompensacion == 1)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
                                                </svg>
                                                Dinero
                                            </span>
                                        @elseif($solicitud->id_tipoCompensacion == 2)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                Tiempo
                                            </span>
                                        @elseif($solicitud->id_tipoCompensacion == 0)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                Tiempo
                                            </span>
                                        @else
                                            <span>{{ $solicitud->id_tipoCompensacion ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-2 py-2 text-center whitespace-nowrap">{{ $solicitud->min_reales ? number_format($solicitud->min_reales, 0) : '-' }}</td>
                                    <td class="px-2 py-2 text-center whitespace-nowrap">{{ $solicitud->min_25 ? number_format($solicitud->min_25, 0) : '-' }}</td>
                                    <td class="px-2 py-2 text-center whitespace-nowrap">{{ $solicitud->min_50 ? number_format($solicitud->min_50, 0) : '-' }}</td>
                                    <td class="px-2 py-2 text-center font-semibold whitespace-nowrap">{{ $solicitud->total_min ? number_format($solicitud->total_min, 0) : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                        No hay solicitudes registradas
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </x-sistema.layout>

    <!-- Modal de estados -->
    @if($modalEstadosVisible)
        <x-sistema.modal-estados :estados-solicitud="$estadosSolicitud" :modal-estados-visible="$modalEstadosVisible" />
    @endif

</section>
