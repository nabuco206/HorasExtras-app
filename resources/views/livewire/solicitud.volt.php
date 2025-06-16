<livewire:volt>

<section class="w-full">
    @include('partials.settings-heading') {{-- o tu menú si es otro --}}

    <x-settings.layout :heading="__('Solicitud HE')" :subheading="__('Formulario para ingresar nueva solicitud')">
        <form wire:submit="guardar" class="my-6 w-full space-y-6">
            <flux:input wire:model="nombre" label="Nombre de solicitante" required />
            <flux:input wire:model="motivo" label="Motivo" />

            <div class="flex items-center gap-4">
                <flux:button type="submit" variant="primary">Guardar</flux:button>

                <x-action-message class="me-3" on="guardado">
                    {{ __('Guardado correctamente.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>

@code
    public string $nombre = '';
    public string $motivo = '';

    function guardar() {
        // Aquí puedes guardar en BD, etc.
        $this->dispatch('guardado');
    }
@endcode
