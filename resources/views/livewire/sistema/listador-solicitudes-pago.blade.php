<section class="w-full min-h-screen">
    <div class="mb-4 flex gap-2">
        <input wire:model.debounce.300ms="search" placeholder="Buscar por usuario u observaciones" class="border p-2 flex-1" />
        <select wire:model="estadoId" class="border p-2">
            <option value="">-- Todos los estados --</option>
            @foreach($estados as $e)
                <option value="{{ $e->id }}">{{ $e->descripcion }}</option>
            @endforeach
        </select>
    <button onclick="exportarCSV()" class="px-3 py-2 bg-green-500 text-white">Exportar a Excel</button>
    </div>

    <table class="min-w-full border">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2">ID</th>
                <th class="p-2">Usuario</th>
                <th class="p-2">Fiscalía</th>
                <th class="p-2">Fecha</th>
                <th class="p-2">Horas</th>
                <th class="p-2">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($solicitudes as $s)
                <tr class="border-t hover:bg-gray-50 cursor-pointer" wire:click="mostrarDetalle({{ $s->id }})">
                    <td class="p-2">{{ $s->id }}</td>
                    <td class="p-2">{{ $s->username }}</td>
                    <td class="p-2">{{ $s->cod_fiscalia }}</td>
                    <td class="p-2">{{ $s->fecha_solicitud }}</td>
                    <td class="p-2">{{ $s->minutos_solicitados }} min</td>
                    <td class="p-2">{{ $s->estado?->descripcion ?? $s->id_estado }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">
        {{ $solicitudes->links() }}
    </div>

    @if($solicitudes->isEmpty())
        <div class="mt-4 text-center text-gray-600">No se encontraron solicitudes con los filtros seleccionados.</div>
    @endif

    <!-- Modal simple para mostrar detalles -->
    <div id="solicitud-modal" style="display:none;" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-4 w-2/3">
            <h3 class="text-lg font-bold">Detalles solicitud</h3>
            @if($selected)
                <p><strong>ID:</strong> {{ $selected->id }}</p>
                <p><strong>Usuario:</strong> {{ $selected->username }}</p>
                <p><strong>Fiscalía:</strong> {{ $selected->cod_fiscalia }}</p>
                <p><strong>Fecha:</strong> {{ $selected->fecha_solicitud }}</p>
                <p><strong>Horas:</strong> {{ $selected->minutos_solicitados }} min</p>
                <p><strong>Observaciones:</strong> {{ $selected->observaciones }}</p>
            @endif
            <div class="mt-4 text-right">
                <button onclick="document.getElementById('solicitud-modal').style.display='none'" class="px-3 py-1 bg-gray-200">Cerrar</button>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('show-solicitud-modal', function() {
            document.getElementById('solicitud-modal').style.display = 'flex';
        });

        function exportarCSV() {
            const params = new URLSearchParams();
            const search = document.querySelector('input[wire\:model\="search"]').value;
            const estado = document.querySelector('select[wire\:model\="estadoId"]').value;
            if (search) params.append('search', search);
            if (estado) params.append('estadoId', estado);
            window.location = '/sistema/solicitudes-pago/export?' + params.toString();
        }
    </script>
</section>
