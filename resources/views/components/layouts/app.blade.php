<div>
    <x-layouts.app.sidebar :title="$title ?? null">
        <!-- @php
             Log::info('Renderizando diseño x-layouts.app con contenido:', ['slot' => $slot]);
        @endphp -->
        <flux:main>
            {{ $slot }}
        </flux:main>
    </x-layouts.app.sidebar>
</div>
