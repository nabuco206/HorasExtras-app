@php
    $groups = [
        'Platform' => [
            [
                'name' => 'Dashboard',
                'icon' => 'home',
                'url' => Route::has('dashboard') ? route('dashboard') : '#',
                'current' => Route::has('dashboard') && request()->routeIs('dashboard')
            ],
            [
                'name' => 'Ingreso Horas Extraordinarias',
                'icon' => 'inbox-arrow-down',
                'url' => Route::has('sistema.ingreso-he') ? route('sistema.ingreso-he') : '#',
                'current' => Route::has('sistema.ingreso-he') && request()->routeIs('sistema.ingreso-he')
            ],
           
        ],
        'Administración UDP' => [
            [
                'name' => 'Panel de Admin',
                'icon' => 'shield-check',
                'url' => '/admin',
                'current' => false,
                'target' => '_blank'
            ],
           
        ]
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-r border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="mr-5 flex items-center space-x-2" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                @foreach ($groups as $group => $links)
                    <flux:navlist.group :heading="$group" class="grid">
                        @foreach ($links as $link)
                            @if(isset($link['submenu']))
                                <flux:dropdown position="right" class="w-full" :close-on-click="false">
                                    <flux:navlist.item 
                                        :active="collect($link['submenu'])->contains(fn($sub) => $sub['current'])"
                                        as="button"
                                        class="w-full hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors"
                                        icon="{{ $link['icon'] }}" 
                                        >
                                        <div class="flex justify-between items-center w-full">
                                            <span>{{ $link['name'] }}</span>
                                            <div class="flex items-center gap-1">
                                                @if(!empty($link['iconTrailing']))
                                                    <flux:icon name="{{ $link['iconsubmenu'] }}" class="w-4 h-4 text-zinc-500" />
                                                @endif
                                                <!-- <flux:icon name="bars-3" variant="micro" /> -->
                                            </div>
                                        </div>
                                    </flux:navlist.item>
                                    
                                    <flux:menu slot="dropdown" class="ml-2 w-48 border-l-2 border-zinc-200 dark:border-zinc-700">
                                        @foreach($link['submenu'] as $subitem)
                                            @if(isset($subitem['target']) && $subitem['target'] === '_blank')
                                                <flux:menu.item 
                                                    :href="$subitem['url']" 
                                                    :active="$subitem['current']"
                                                    target="_blank"
                                                    class="pl-4 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                >
                                                    {{ $subitem['name'] }}
                                                </flux:menu.item>
                                            @else
                                                <flux:menu.item 
                                                    :href="$subitem['url']" 
                                                    :active="$subitem['current']"
                                                    wire:navigate
                                                    class="pl-4 hover:bg-zinc-100 dark:hover:bg-zinc-700"
                                                >
                                                    {{ $subitem['name'] }}
                                                </flux:menu.item>
                                            @endif
                                        @endforeach
                                    </flux:menu>
                                </flux:dropdown>
                            @else
                                @if(!empty($link['iconTrailing']))
                                    @if(isset($link['target']) && $link['target'] === '_blank')
                                        <flux:navlist.item 
                                            :href="$link['url']" 
                                            :current="$link['current']" 
                                            target="_blank"
                                        >
                                            <div class="flex justify-between items-center w-full">
                                                <span>{{ $link['name'] }}</span>
                                                <flux:icon name="{{ $link['icon'] }}" class="w-4 h-4 text-zinc-500" />
                                            </div>
                                        </flux:navlist.item>
                                    @else
                                        <flux:navlist.item 
                                            :href="$link['url']" 
                                            :current="$link['current']" 
                                            wire:navigate
                                        >
                                            <div class="flex justify-between items-center w-full">
                                                <span>{{ $link['name'] }}</span>
                                                <flux:icon name="{{ $link['icon'] }}" class="w-4 h-4 text-zinc-500" />
                                            </div>
                                        </flux:navlist.item>
                                    @endif
                                @else
                                    @if(isset($link['target']) && $link['target'] === '_blank')
                                        <flux:navlist.item 
                                            :icon="$link['icon']" 
                                            :href="$link['url']" 
                                            :current="$link['current']" 
                                            target="_blank"
                                        >
                                            {{ $link['name'] }}
                                        </flux:navlist.item>
                                    @else
                                        <flux:navlist.item 
                                            :icon="$link['icon']" 
                                            :href="$link['url']" 
                                            :current="$link['current']" 
                                            wire:navigate
                                        >
                                            {{ $link['name'] }}
                                        </flux:navlist.item>
                                    @endif
                                @endif
                            @endif
                        @endforeach
                    </flux:navlist.group>
                @endforeach
            </flux:navlist>


            <flux:spacer />

            <!-- <flux:navlist variant="outline">
                <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                    {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits" target="_blank">
                    {{ __('Documentation') }}
                </flux:navlist.item>
            </flux:navlist> -->

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start">
                <flux:profile
                    :name="auth()->user()->name"
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

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

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
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

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