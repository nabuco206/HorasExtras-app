<?php
use App\Services\DashboardService;

    $user = auth()->user();
    $rol = $user->id_rol;
    // var_dump($rol);
    $cod_fiscalia = $user->cod_fiscalia;
    $esLider = $user->flag_lider ?? false;

    // if($esLider){
    //     // Log::info('Es lider de fiscalia: ' . $cod_fiscalia);
    //     $rol = 6;
    // }
    $dashboardService = new DashboardService();
    $stats = $dashboardService->obtenerEstadisticasPendientes($rol, $user, $cod_fiscalia);
    $badges = [
        'JD_aprobar_he' => $stats['pendientesComp'] ?? 0,
        'JD_aprobar_pago' => $stats['pendientesPago'] ?? 0,
        'JD_aprobar_compensacion' => $stats['cantidadSolicitudesCompensa'] ?? 0,
        // Puedes agregar más códigos aquí si necesitas más badges
    ];
     

    // Acceder a la fiscalía asociada
    //$gls_fiscalia = $user->fiscalia->gls_fiscalia ?? 'Sin fiscalía';

    // Obtener configuración de menú para el rol y deduplicar por route|url|name
    $rawMenu = config('menu.roles.' . $rol, []);

    // var_dump($rawMenu);
    $menuConfig = collect($rawMenu)->unique(function($item) {
        // Combina los campos relevantes para deduplicar correctamente
        return (
            ($item['route'] ?? '') . '|' .
            ($item['url'] ?? '') . '|' .
            ($item['name'] ?? '')
        );
    })->values()->all();

    // Detectar la ruta actual y buscar el menú activo
    $currentRoute = \Route::currentRouteName();
    $menuActual = collect($menuConfig)->first(function($item) use ($currentRoute) {
        return isset($item['route']) && $item['route'] === $currentRoute;
    });


?>
    <!DOCTYPE html>
    <html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
        <head>
            @include('partials.head', ['title' => ''])
        </head>
        <body class="min-h-screen bg-white dark:bg-zinc-800">
            <flux:sidebar sticky stashable class="border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

                <a href="#" class="mr-5 flex items-center space-x-2" wire:navigate>
                    <x-app-logo />
                </a>
                <flux:navlist variant="outline">
                    @php
                        // Agrupar por 'section' (si no existe usar 'General')
                        $grouped = collect($menuConfig)->groupBy(function($item){
                            return $item['section'] ?? 'General';
                        });
                    @endphp

                    @foreach ($grouped as $section => $items)
                        {{-- Mostrar título de sección si no es 'General' o si quieres siempre --}}
                        <div class="px-3 py-2 text-xs font-semibold text-zinc-500">{{ $section }}</div>

                        @foreach ($items as $menuItem)
                            @php
                                $params = $menuItem['params'] ?? [];
                                if (isset($menuItem['titulo'])) {
                                    $params['titulo'] = $menuItem['titulo'];
                                }
                            @endphp

                            <flux:navlist.item
                                :icon="$menuItem['icon']"
                                :href="isset($menuItem['route']) && $menuItem['route'] ? route($menuItem['route'], $params) : ($menuItem['url'] ?? '#')"
                                :current="isset($menuItem['route']) && $menuItem['route'] ? (request()->routeIs($menuItem['route']) || request()->is(trim(route($menuItem['route'], $params), '/'))) : false"
                                target="{{ $menuItem['target'] ?? '_self' }}"
                            >
                                {{ $menuItem['name'] }}
                                @php $codigo = $menuItem['codigo'] ?? null; @endphp
                                @if(isset($badges[$codigo]) && $badges[$codigo] > 0)
                                    <span class="ml-2 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                                        ({{ $badges[$codigo] }})
                                    </span>
                                @endif
                            </flux:navlist.item>
                        @endforeach
                    @endforeach
                </flux:navlist>


                <flux:spacer />

               
                <!-- Desktop User Menu -->
                <span class="p-0 text-sm font-normal">Version 0.1</span>
                <flux:dropdown position="bottom" align="start">
                    <flux:profile
                        :name="auth()->user()->name"
                        :cod_fl="auth()->user()->cod_fiscalia"
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevrons-up-down"
                    />

                    <flux:menu class="w-[220px]">
                        <flux:menu.radio.group>
                            @if(auth()->check())
                                <div class="p-0 text-sm font-normal">
                                    <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                        <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                            <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                                {{ auth()->user()->initials() }}
                                            </span>
                                        </span>

                                        <div class="grid flex-1 text-left text-sm leading-tight">
                                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                            <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        {{-- Settings temporalmente comentado (sidebar principal) --}}
                        {{-- <flux:menu.radio.group>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group> --}}

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </flux:sidebar>

            <!-- Mobile User Menu -->
            <flux:header class="lg:hidden">
                <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

                <flux:spacer />

                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            @if(auth()->check())
                                <div class="p-0 text-sm font-normal">
                                    <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                        <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                            <span class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
                                                {{ auth()->user()->initials() }}
                                            </span>
                                        </span>

                                        <div class="grid flex-1 text-left text-sm leading-tight">
                                            <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                            <span class="truncate text-xs">{{ $cod_fiscalia }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        {{-- Settings temporalmente comentado (header móvil) --}}
                        {{-- <flux:menu.radio.group>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group> --}}

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            </flux:header>

            
            
            {{ $slot }}

            @fluxScripts
        </body>
    </html>
</div>

@php
   //Log::debug('DEBUG MENU CONFIG', [
        //'user_id' => auth()->id(),
        //'rol' => $rol,
       // 'cod_fiscalia' => $cod_fiscalia,
       // 'gls_fiscalia' => $gls_fiscalia,
    //    'attributes' => auth()->user()->toArray(),
      //]);
@endphp

