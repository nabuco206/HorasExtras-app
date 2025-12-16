<!--Subida de archivo adjunto -->
<div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Adjuntar Documento</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Sube un archivo. Máx. 5MB.
    </p>

    <div class="mt-4">
        @if($archivoCargado)
            <div class="flex items-center space-x-3 text-sm text-green-600 bg-green-50 dark:bg-green-900/30 dark:text-green-400 p-2 rounded">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ $archivoCargado->getClientOriginalName() }}</span>
            </div>
        @endif

        {{-- Mostrar tabla con datos del Excel --}}
        @if(!empty($excelData))
            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            @foreach($excelData[0] as $header)
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">{{ $header }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach(array_slice($excelData, 1) as $row)
                            <tr>
                                @foreach($row as $i => $cell)
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700 dark:text-gray-200">
                                        @if($loop->parent->index === 1 && $loop->index === 2)
                                            {{-- No mostrar nada especial en la cabecera --}}
                                        @endif
                                        @if($loop->parent->index === 1 && $loop->index === 2)
                                            {{-- No mostrar nada especial en la cabecera --}}
                                        @endif
                                        @if($i === 1 && is_numeric($cell))
                                            {{ \Carbon\Carbon::createFromTimestampUTC(($cell - 25569) * 86400)->format('Y-m-d') }}
                                        @else
                                            {{ $cell }}
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="mt-4">
                    <button wire:click="guardarPagosConcretados" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Guardar en base de datos</button>
                </div>
            </div>
        @endif

        @if (session()->has('success'))
            <div class="mt-2 text-green-600">{{ session('success') }}</div>
        @endif
        @if (session()->has('error'))
            <div class="mt-2 text-red-600">{{ session('error') }}</div>
        @endif

        <div class="mt-2">
            <label class="block">
                <span class="sr-only">Elegir archivo</span>
                <input
                    type="file"
                    wire:model="archivoAdjunto"
                    accept=".xls,.xlsx,."
                    class="block w-full text-sm text-gray-500
                           file:mr-4 file:py-2 file:px-4
                           file:rounded-md file:border-0
                           file:text-sm file:font-semibold
                           file:bg-indigo-50 file:text-indigo-700
                           hover:file:bg-indigo-100
                           dark:file:bg-indigo-900/50 dark:file:text-indigo-300"
                />
            </label>

            <!-- Mensaje de carga -->
            @if($cargandoArchivo)
                <div class="mt-2 flex items-center text-sm text-indigo-600 dark:text-indigo-400">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Cargando...
                </div>
            @endif

            <!-- Errores de validación -->
            @error('archivoAdjunto')
                <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>