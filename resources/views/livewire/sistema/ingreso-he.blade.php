<?php

use App\Models\User;
use App\Models\TblTipoTrabajo;
use App\Models\TblEstado;
use App\Models\TblSolicitudHe;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Services\FlujoEstadoService;

new class extends Component {
    use WithFileUploads;

    public string $username = '';
    public int $id_tipo_trabajo = 0;
    public string $fecha = '';
    public string $hrs_inicial = '';
    public string $hrs_final = '';
    public bool $propone_pago = false;
    public $tipos_trabajo = [];
    public $estados = [];
    public $solicitudes = [];
    public $modalEstadosVisible = false;
    public $estadosSolicitud = [];
    public $archivo_adjunto;

    // Nuevas propiedades para el modal de documentos
    public $modalDocumentoVisible = false;
    public $documentoActual = null;
    public $tipoDocumento = null;
    public $nombreDocumento = null;

    // Propiedades del bolsón
    public int $saldoDisponible = 0;
    public int $saldoPendiente = 0;
    public array $detalleBolson = [];
    public array $resumenCompleto = [];
    public int $bolsonesProximosVencer = 0;


    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->username = Auth::user()->name;
        $this->tipos_trabajo = TblTipoTrabajo::all();
        $this->estados = TblEstado::all();
        $this->solicitudes = \App\Models\TblSolicitudHe::orderByDesc('id')->get();
        $this->propone_pago = false;

        // Cargar datos del bolsón
        $this->cargarDatosBolson();
    }

    /**
     * Cargar datos del bolsón de tiempo
     */
    public function cargarDatosBolson(): void
    {
        $bolsonService = app(\App\Services\BolsonService::class);

        // Obtener resumen completo (disponibles y pendientes)
        $this->resumenCompleto = $bolsonService->obtenerResumenCompleto($this->username);
        $this->saldoDisponible = $this->resumenCompleto['total_disponible'];
        $this->saldoPendiente = $this->resumenCompleto['total_pendiente'];
        $this->detalleBolson = $bolsonService->obtenerDetalleSaldo($this->username);

        // Contar bolsones próximos a vencer (30 días)
        $this->bolsonesProximosVencer = \App\Models\TblBolsonTiempo::where('username', $this->username)
            ->where('activo', true)
            ->where('estado', 'DISPONIBLE')
            ->where('saldo_min', '>', 0)
            ->where('fecha_vence', '<=', \Carbon\Carbon::now()->addDays(30))
            ->where('fecha_vence', '>=', \Carbon\Carbon::now())
            ->count();
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

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function saveSolicitud(): void
    {
        $validated = $this->validate([
            'id_tipo_trabajo' => ['required', 'integer', 'exists:tbl_tipo_trabajo,id'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'hrs_inicial' => ['required', 'date_format:H:i'],
            'hrs_final' => ['required', 'date_format:H:i', 'after:hrs_inicial'],
            'propone_pago' => ['boolean'],
            'archivo_adjunto' => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf,doc,docx', 'max:5120'], // 5MB máximo
        ]);

        if ($this->hrs_final <= $this->hrs_inicial) {
            session()->flash('error', 'La hora de salida debe ser mayor que la hora de ingreso y no puede cruzar las 00:00 hrs.');
            return;
        }

        $validated['id_tipo_compensacion'] = $this->propone_pago ? 1 : 0;
        $validated['username'] = Auth::user()->name;
        $validated['cod_fiscalia'] = Auth::user()->cod_fiscalia;

        try {
            $solicitudHeService = app(\App\Services\SolicitudHeService::class);
            $resultado = $solicitudHeService->calculaPorcentaje(
                $this->fecha,
                $this->hrs_inicial,
                $this->hrs_final
            );

            $validated['fecha_evento'] = $this->fecha;
            $validated['hrs_inicio'] = $this->hrs_inicial;
            $validated['hrs_fin'] = $this->hrs_final;
            $validated['min_reales'] = $resultado['min_reales'];
            $validated['min_25'] = $resultado['min_25'];
            $validated['min_50'] = $resultado['min_50'];
            $validated['total_min'] = $resultado['total_min'];

            // Intentar obtener el estado inicial desde el flujo de compensación HE
            // Preparar estado pendiente por defecto (se usa como fallback y en el log)
            $estadoPendiente = \App\Models\TblEstado::where('codigo', 'INGRESADO')->first();

            // Seleccionar flujo según si el usuario propone pago (dinero) o compensación (tiempo)
            $codigoFlujo = $this->propone_pago ? 'HE_DINERO' : 'HE_COMPENSACION';
            $flujo = \App\Models\TblFlujo::where('codigo', $codigoFlujo)->first();
            $estadoInicial = $flujo ? $flujo->estadoInicial : null;

            if ($estadoInicial && isset($estadoInicial->id)) {
                $validated['id_estado'] = $estadoInicial->id;
            } else {
                // Fallback al estado INGRESADO por compatibilidad
                $validated['id_estado'] = $estadoPendiente ? $estadoPendiente->id : 1;
            }

            // Procesar archivo adjunto
            if ($this->archivo_adjunto) {
                $extension = $this->archivo_adjunto->getClientOriginalExtension();
                $fechaCompleta = date('Y-m-d');
                $nombreArchivo = $fechaCompleta . '_' . time() . '_' . $this->username . '.' . $extension;

                // Guardar el archivo
                $rutaArchivo = $this->archivo_adjunto->storeAs('solicitudes-he', $nombreArchivo, 'public');

                $validated['archivo_adjunto'] = $nombreArchivo;
                $validated['nombre_archivo_original'] = $this->archivo_adjunto->getClientOriginalName();
            }

        } catch (\Exception $e) {
            $validated['fecha_evento'] = $this->fecha;
            $validated['hrs_inicio'] = $this->hrs_inicial;
            $validated['hrs_fin'] = $this->hrs_final;
            $validated['min_reales'] = 0;
            $validated['min_25'] = 0;
            $validated['min_50'] = 0;
            $validated['total_min'] = 0;
        }


        $nuevaSolicitud = \App\Models\TblSolicitudHe::create($validated);

        // Registrar seguimiento usando el logger
        \App\Services\SeguimientoSolicitudLogger::log(
            $nuevaSolicitud->id,
            $this->username,
            $nuevaSolicitud->id_estado ?: $estadoPendiente->id
        );

        // === CREAR BOLSON PENDIENTE PARA HE DE COMPENSACION ===
        if ($nuevaSolicitud->id_tipo_compensacion == 0 && $nuevaSolicitud->total_min > 0) {
            $flujoService = app(\App\Services\FlujoEstadoService::class);
            $flujoService->crearBolsonPendienteParaSolicitud($nuevaSolicitud);
        }

        $this->solicitudes = \App\Models\TblSolicitudHe::orderByDesc('id')->get();

        // Recargar datos del bolsón después de guardar
        $this->cargarDatosBolson();

        $this->dispatch('profile-updated', name: $this->username);
    }

    public function verDocumento($solicitudId)
    {
        $solicitud = TblSolicitudHe::find($solicitudId);

        if (!$solicitud || !$solicitud->tieneArchivo()) {
            $this->dispatch('error', message: 'Documento no encontrado');
            return;
        }

        $this->documentoActual = asset('storage/solicitudes-he/' . $solicitud->archivo_adjunto);
        $this->nombreDocumento = $solicitud->nombre_archivo_original;

        // Determinar tipo de documento
        $extension = pathinfo($solicitud->archivo_adjunto, PATHINFO_EXTENSION);
        $this->tipoDocumento = strtolower($extension);

        $this->modalDocumentoVisible = true;
    }

    public function cerrarModalDocumento()
    {
        $this->modalDocumentoVisible = false;
        $this->documentoActual = null;
        $this->tipoDocumento = null;
        $this->nombreDocumento = null;
    }

    public function descargarDocumento()
    {
        if ($this->documentoActual) {
            // Cambiar de redirect a dispatch con JavaScript
            $this->dispatch('open-in-new-tab', url: $this->documentoActual);
        }
    }


}; ?>

<section class="w-full">

    <!-- <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Ingreso Hora Extra') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Manage your profile and account settings') }}</flux:subheading>
        <flux:separator variant="subtle" /> -->
    <!-- </div> -->

    <x-sistema.layout :heading="__('Ingreso Hora Extra')">
        <!-- Formulario -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6 mb-8 max-w-6xl">
            <form wire:submit="saveSolicitud" class="w-full space-y-6" enctype="multipart/form-data">
                    <flux:input wire:model="username" type="hidden" required readonly />
                    <div class="flex gap-4 items-end">
                        <flux:select wire:model="id_tipo_trabajo" :label="__('Tipo de Trabajo')" class="flex-1 max-w-md" required>
                            <option value="">Seleccione...</option>
                            @foreach($tipos_trabajo as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->gls_tipo_trabajo }}</option>
                            @endforeach
                        </flux:select>
                        <flux:input wire:model="fecha" :label="__('Fecha')" type="date" class="flex-1" required />
                        <flux:input wire:model="hrs_inicial" :label="__('Hora Ingreso')" type="time" class="flex-1" required />
                        <flux:input wire:model="hrs_final" :label="__('Hora Salida')" type="time" class="flex-1" required />
                        <div class="flex items-end">
                            <flux:checkbox wire:model="propone_pago" :label="__('Propone Pago')" />
                        </div>
                    </div>
                     <!-- Campo de archivo actualizado -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-200 mb-1">Adjuntar archivo</label>
                        <input
                            type="file"
                            wire:model="archivo_adjunto"
                            class="w-full text-xs border border-gray-300 dark:border-gray-600 rounded-md p-2 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                            accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
                        >
                        <span class="text-xs text-gray-400">* Adjuntar imagen del control horario (JPG, PNG, PDF, DOC - Máx: 5MB)</span>

                        @error('archivo_adjunto')
                            <span class="text-red-500 text-xs mt-1">{{ $message }}</span>
                        @enderror

                        <!-- Indicador de carga -->
                        <div wire:loading wire:target="archivo_adjunto" class="text-xs text-blue-600 mt-1">
                            Cargando archivo...
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-4">
                        <flux:button variant="primary" type="submit" class="px-8">{{ __('Ingresar') }}</flux:button>
                        <x-action-message class="me-3" on="profile-updated">
                            {{ __('Guardado !!!.') }}
                        </x-action-message>
                    </div>
                </form>
            </div>
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
                                    <div class="flex items-center space-x-2">
                                        <p class="text-xs text-emerald-700 dark:text-emerald-300">{{ $saldoDisponible }} min disponibles</p>
                                        @if($saldoPendiente > 0)
                                            <span class="bg-yellow-200 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200 px-1.5 py-0.5 rounded-full text-xs font-medium">
                                                +{{ $saldoPendiente }} pendientes
                                            </span>
                                        @endif
                                    </div>
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
                                        <div class="text-emerald-700 dark:text-emerald-300 font-medium">Disponible</div>
                                        <div class="text-emerald-900 dark:text-emerald-100 font-bold">{{ $saldoDisponible }} min</div>
                                    </div>
                                    <div>
                                        <div class="text-emerald-700 dark:text-emerald-300 font-medium">Por Aprobar</div>
                                        <div class="text-emerald-900 dark:text-emerald-100 font-bold">{{ $saldoPendiente }} min</div>
                                    </div>
                                </div>
                                @if($saldoPendiente > 0)
                                    <div class="mt-2 pt-2 border-t border-emerald-300/50 dark:border-emerald-600/50">
                                        <div class="text-emerald-700 dark:text-emerald-300 font-medium">Total Proyectado</div>
                                        <div class="text-emerald-900 dark:text-emerald-100 font-bold">{{ $saldoDisponible + $saldoPendiente }} min</div>
                                    </div>
                                @endif
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

                            <!-- Bolsones pendientes -->
                            @if($saldoPendiente > 0 && count($resumenCompleto['detalle_pendientes']) > 0)
                                <div class="bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 rounded-lg p-3">
                                    <h5 class="text-xs font-medium text-yellow-800 dark:text-yellow-200 mb-2 flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Tiempo por aprobar:
                                    </h5>
                                    <div class="space-y-1">
                                        @foreach($resumenCompleto['detalle_pendientes'] as $bolson)
                                            <div class="bg-yellow-50 dark:bg-yellow-800/20 rounded-md p-2 text-xs">
                                                <div class="flex justify-between items-center">
                                                    <span class="font-medium text-yellow-900 dark:text-yellow-100">HE #{{ $bolson['solicitud_he_id'] }}</span>
                                                    <span class="text-yellow-700 dark:text-yellow-300">{{ $bolson['minutos_pendientes'] }} min</span>
                                                </div>
                                                <div class="text-yellow-600 dark:text-yellow-400 mt-1">
                                                    Ingresado: {{ \Carbon\Carbon::parse($bolson['fecha_crea'])->format('d/m/Y') }}
                                                </div>
                                            </div>
                                        @endforeach
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



            <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 w-full">
                <div class="p-6 border-b border-neutral-200 dark:border-neutral-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Solicitudes Ingresadas</h3>
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
                                    <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Tipo Comp.</th>

                                    <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Min. Reales</th>
                                    <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Min. 25%</th>
                                    <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Min. 50%</th>
                                    <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Total Min.</th>
                                    <th scope="col" class="px-2 py-2 text-center whitespace-nowrap">Documento</th>
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
                                                {{ $estado ? $estado->descripcion : '-' }}
                                            </td>
                                            <td class="px-2 py-2 text-center whitespace-nowrap">
                                                @if($solicitud->id_tipo_compensacion == 1)
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
                                                        </svg>
                                                        Dinero
                                                    </span>
                                                @elseif($solicitud->id_tipo_compensacion == 0)
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
                                             <!-- Columna de documento actualizada -->
                                            <td class="px-2 py-2 text-center whitespace-nowrap">
                                                @if($solicitud->tieneArchivo())
                                                    <button wire:click="verDocumento({{ $solicitud->id }})"
                                                            class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900 rounded-full transition-colors duration-200"
                                                            title="Ver documento: {{ $solicitud->nombre_archivo_original }}">
                                                        @php
                                                            $extension = pathinfo($solicitud->archivo_adjunto, PATHINFO_EXTENSION);
                                                        @endphp

                                                        @if(in_array(strtolower($extension), ['jpg', 'jpeg', 'png']))
                                                            <!-- Icono para imágenes -->
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                        @elseif(strtolower($extension) === 'pdf')
                                                            <!-- Icono para PDF -->
                                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                                                            </svg>
                                                        @elseif(in_array(strtolower($extension), ['doc', 'docx']))
                                                            <!-- Icono para documentos Word -->
                                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                                                <path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20Z"></path>
                                                            </svg>
                                                        @else
                                                            <!-- Icono genérico -->
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                            </svg>
                                                        @endif
                                                    </button>
                                                @else
                                                    <span class="inline-flex items-center justify-center w-8 h-8 text-gray-400">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </span>
                                                @endif
                                            </td>
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
        </div>

        <!-- Modal para ver documentos -->
        @if($modalDocumentoVisible)
            <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('modalDocumentoVisible') }">
                <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <!-- Overlay -->
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cerrarModalDocumento"></div>

                    <!-- Modal -->
                    {{-- <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full"> --}}
                    <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-7xl sm:w-full">
                        <!-- Header -->
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">
                                    {{ $nombreDocumento }}
                                </h3>
                                <div class="flex space-x-2">
                                    <button wire:click="descargarDocumento"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Descargar
                                    </button>
                                    <button wire:click="cerrarModalDocumento"
                                            class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                        Cerrar
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Contenido del documento -->
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-5 sm:p-6">
                            <div class="max-h-96 overflow-auto">
                                @if(in_array($tipoDocumento, ['jpg', 'jpeg', 'png']))
                                    <!-- Vista para imágenes -->
                                    <div class="text-center">
                                        <img src="{{ $documentoActual }}"
                                             alt="{{ $nombreDocumento }}"
                                             class="max-w-full h-auto rounded-lg shadow-lg mx-auto">
                                    </div>
                                @elseif($tipoDocumento === 'pdf')
                                    <!-- Vista para PDF -->
                                    <div class="text-center">
                                        <iframe src="{{ $documentoActual }}"
                                                class="w-full h-96 border rounded-lg"
                                                frameborder="0">
                                        </iframe>
                                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                            Si el PDF no se muestra correctamente, haz clic en "Descargar" para verlo en tu navegador.
                                        </p>
                                    </div>
                                @else
                                    <!-- Vista para otros documentos -->
                                    <div class="text-center py-8">
                                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                            Documento: {{ $nombreDocumento }}
                                        </h4>
                                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                                            Este tipo de archivo no se puede previsualizar en el navegador.
                                        </p>
                                        <button wire:click="descargarDocumento"
                                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                            Descargar para ver
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </x-sistema.layout>

    <!-- Modal de estados -->
    @if($modalEstadosVisible)
        <x-sistema.modal-estados :estados-solicitud="$estadosSolicitud" :modal-estados-visible="$modalEstadosVisible" />
    @endif

</section>

<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('open-in-new-tab', (event) => {
        window.open(event.url, '_blank');
    });
});
</script>
