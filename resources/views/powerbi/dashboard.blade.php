@php
    $embedUrl = $embedUrl ?? null;
@endphp

<x-layouts.app>
    <div class="flex min-h-[70vh]">
        <aside class="hidden md:block w-64">
            <x-layouts.app.sidebar />
        </aside>

        <main class="flex-1 p-4">
            <div class="max-w-[1200px] mx-auto">
                <div class="bg-white dark:bg-zinc-800 rounded shadow overflow-hidden">
                    <div class="p-3 border-b">
                        <h2 class="text-lg font-semibold">Gasto en HE 2025</h2>
                    </div>

                    <div class="p-4">
                        <div class="pb-wrapper relative w-full" style="padding-bottom:56.25%; height:0;">
                            @if(!empty($embedUrl))
                                <iframe title="Reporte2025_He"
                                        src="{{ $embedUrl }}"
                                        allowfullscreen="true"
                                        frameborder="0"
                                        style="position:absolute;top:0;left:0;width:100%;height:100%;border:0">
                                </iframe>
                            @else
                                <div id="reportContainer" style="position:absolute;top:0;left:0;width:100%;height:100%;"></div>
                                <div class="text-sm text-gray-600 mt-2">No hay URL público configurado. El sistema intentará obtener embed token si está disponible.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layouts.app>
