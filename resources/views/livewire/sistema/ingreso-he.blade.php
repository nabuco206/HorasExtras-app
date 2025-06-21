<?php

use App\Models\User;
use App\Models\TblTipoTrabajo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public string $username = '';
    public int $id_tipo_trabajo = 0;
    public string $fecha = '';
    public string $hrs_inicial = '';
    public string $hrs_final = '';
    public $tipos_trabajo = [];
    public $solicitudes = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->username = Auth::user()->name;
        $this->tipos_trabajo = TblTipoTrabajo::all();
        $this->solicitudes = \App\Models\TblSolicitudHe::orderByDesc('id')->get();
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function saveSolicitud(): void
    {
        $validated = $this->validate([
            'username' => ['required', 'string'],
            'id_tipo_trabajo' => ['required', 'integer', 'exists:tbl_tipo_trabajo,id'],
            'fecha' => ['required', 'date'],
            'hrs_inicial' => ['required'],
            'hrs_final' => ['required'],
        ]);

        $validated['tipo_solicitud'] = 0;
        $validated['id_tipoCompensacion'] = 0;
        

        \App\Models\TblSolicitudHe::create($validated);

        $this->solicitudes = \App\Models\TblSolicitudHe::orderByDesc('id')->get();

        $this->dispatch('profile-updated', name: $this->username);
    }
}; ?>

<section class="w-full">
    <!-- <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1">{{ __('Ingreso Hora Extra') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6">{{ __('Manage your profile and account settings') }}</flux:subheading> 
        <flux:separator variant="subtle" /> -->
    <!-- </div> -->

    <x-sistema.layout :heading="__('Ingreso Hora Extra')">
        <form wire:submit="saveSolicitud" class="my-6 w-full space-y-6" enctype="multipart/form-data">
            <flux:input wire:model="username" :label="__('Usuario')" type="text" required readonly />
            <flux:select wire:model="id_tipo_trabajo" :label="__('Tipo de Trabajo')" required>
                <option value="">Seleccione...</option>
                @foreach($tipos_trabajo as $tipo)
                    <option value="{{ $tipo->id }}">{{ $tipo->gls_tipo_trabajo }}</option>
                @endforeach
            </flux:select>
            <div class="flex gap-4">
                <flux:input wire:model="fecha" :label="__('Fecha')" type="date" class="flex-1" required />
                <flux:input wire:model="hrs_inicial" :label="__('Hora Ingreso')" type="time" class="flex-1" required />
                <flux:input wire:model="hrs_final" :label="__('Hora Salida')" type="time" class="flex-1" required />
            </div>
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Ingresar') }}</flux:button>
                </div>
                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Guardado !!!.') }}
                </x-action-message>
            </div>
        </form>

        <div class="my-8">
            <h2 class="text-xl font-semibold">{{ __('Solicitudes Ingresadas') }}</h2>
            <div class="mt-4 overflow-x-auto rounded-lg border bg-white shadow-sm">
                <table class="min-w-full w-full divide-y divide-gray-200 text-xs md:text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-2 py-2 md:px-4 md:py-3 text-left font-semibold uppercase tracking-wider">ID</th>
                            <th class="px-2 py-2 md:px-4 md:py-3 text-left font-semibold uppercase tracking-wider">{{ __('Usuario') }}</th>
                            <th class="px-2 py-2 md:px-4 md:py-3 text-left font-semibold uppercase tracking-wider">{{ __('Tipo de Trabajo') }}</th>
                            <th class="px-2 py-2 md:px-4 md:py-3 text-left font-semibold uppercase tracking-wider">{{ __('Fecha') }}</th>
                            <th class="px-2 py-2 md:px-4 md:py-3 text-left font-semibold uppercase tracking-wider">{{ __('Hora Ingreso') }}</th>
                            <th class="px-2 py-2 md:px-4 md:py-3 text-left font-semibold uppercase tracking-wider">{{ __('Hora Salida') }}</th>
                            <th class="px-2 py-2 md:px-4 md:py-3 text-left font-semibold uppercase tracking-wider hidden md:table-cell">{{ __('Tipo Solicitud') }}</th>
                            <th class="px-2 py-2 md:px-4 md:py-3 text-left font-semibold uppercase tracking-wider hidden md:table-cell">{{ __('Tipo Compensación') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($solicitudes as $loopIndex => $solicitud)
                            <tr class="@if($loopIndex % 2 == 0) bg-gray-50 @endif hover:bg-blue-50 transition-colors">
                                <td class="whitespace-nowrap px-2 py-2 md:px-4 md:py-3">{{ $solicitud->id }}</td>
                                <td class="whitespace-nowrap px-2 py-2 md:px-4 md:py-3">{{ $solicitud->username }}</td>
                                <td class="whitespace-nowrap px-2 py-2 md:px-4 md:py-3">
                                    @php
                                        $tipo = $tipos_trabajo->firstWhere('id', $solicitud->id_tipo_trabajo);
                                    @endphp
                                    {{ $tipo ? $tipo->gls_tipo_trabajo : '-' }}
                                </td>
                                <td class="whitespace-nowrap px-2 py-2 md:px-4 md:py-3">
                                    @php
                                        $fecha = \Carbon\Carbon::parse($solicitud->fecha)->format('d/m/Y');
                                    @endphp
                                    {{ $fecha }}
                                </td>
                                <td class="whitespace-nowrap px-2 py-2 md:px-4 md:py-3">{{ $solicitud->hrs_inicial }}</td>
                                <td class="whitespace-nowrap px-2 py-2 md:px-4 md:py-3">{{ $solicitud->hrs_final }}</td>
                                <td class="whitespace-nowrap px-2 py-2 md:px-4 md:py-3 hidden md:table-cell">{{ $solicitud->tipo_solicitud }}</td>
                                <td class="whitespace-nowrap px-2 py-2 md:px-4 md:py-3 hidden md:table-cell">{{ $solicitud->id_tipoCompensacion }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-sistema.layout>

    
</section>
