<div class="max-w-lg mx-auto mt-8 bg-white p-6 rounded shadow">
    <h2 class="text-xl font-bold mb-4">Nueva Solicitud de Compensación</h2>
    @if (session()->has('mensaje'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-2">
            {{ session('mensaje') }}
        </div>
    @endif
    <form wire:submit.prevent="save">
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Usuario</label>
            <input type="text" wire:model="username" class="w-full border rounded px-3 py-2" readonly>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Fiscalía</label>
            <input type="number" wire:model="cod_fiscalia" class="w-full border rounded px-3 py-2" readonly>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Fecha</label>
            <input type="date" wire:model="fecha" class="w-full border rounded px-3 py-2" required>
            @error('fecha') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Hora Inicial</label>
            <input type="time" wire:model="hrs_inicial" class="w-full border rounded px-3 py-2" required>
            @error('hrs_inicial') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Hora Final</label>
            <input type="time" wire:model="hrs_final" class="w-full border rounded px-3 py-2" required>
            @error('hrs_final') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Total Minutos</label>
            <input type="number" wire:model="total_min" class="w-full border rounded px-3 py-2">
            @error('total_min') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
    </form>
</div>
