<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Sistema de Horas Extras</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] flex p-6 lg:p-8 items-center lg:justify-center min-h-screen flex-col">
        <img src="{{ asset('images/logo_fiscalia.svg') }}" alt="Logo Fiscalía" class="w-40 h-18 mx-auto mb-4">
        {{-- <header class="w-full lg:max-w-4xl max-w-[335px] text-sm mb-6 not-has-[nav]:hidden">
            @if (Route::has('login'))
                <nav class="flex items-center justify-end gap-4">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="inline-block px-5 py-1.5 dark:text-[#EDEDEC] border-[#19140035] hover:border-[#1915014a] border text-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#62605b] rounded-sm text-sm leading-normal"
                        >
                            Dashboard
                        </a>
                        <a class="text-sm text-blue-700 underline ml-4">Menú Principal</a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button
                                type="submit"
                                class="text-sm text-[#F53003] hover:text-[#d42a02] underline ml-4"
                            >
                                Cerrar Sesión
                            </button>
                        </form>
                    @else
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            Complete el formulario para ingresar
                        </span>
                    @endauth
                </nav>
            @endif
        </header> --}}
        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">
            <main class="flex max-w-[335px] w-full flex-col-reverse lg:max-w-4xl lg:flex-row">
                <div class="text-[13px] leading-[20px] flex-1 p-6 pb-12 lg:p-20 bg-white dark:bg-[#161615] dark:text-[#EDEDEC] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] rounded-bl-lg rounded-br-lg lg:rounded-tl-lg lg:rounded-br-none">
                    <h1 class="mb-1 font-medium">Bienvenido al Sistema de Horas Extras</h1>
                    <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">La Fiscalía Regional de Valparaíso presenta una plataforma moderna, segura y sencilla que optimiza todo el ciclo de vida de las horas extraordinarias. Desde el registro inicial por parte de los funcionarios hasta la aprobación escalonada y las compensaciones, cada etapa se realiza en línea, con total trazabilidad y respaldo normativo.
                    <ul class="flex flex-col mb-4 lg:mb-6">
                        <li class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:top-1/2 before:bottom-0 before:left-[0.4rem] before:absolute">
                            <span class="relative py-1 bg-white dark:bg-[#161615]">
                                <span class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                    <span class="rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A] w-1.5 h-1.5"></span>
                                </span>
                            </span>
                            <span>Registrar solicitudes .</span>
                        </li>
                        <li class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:top-1/2 before:bottom-0 before:left-[0.4rem] before:absolute">
                            <span class="relative py-1 bg-white dark:bg-[#161615]">
                                <span class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                    <span class="rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A] w-1.5 h-1.5"></span>
                                </span>
                            </span>
                            <span>Monitorea tus solicitudes. </span>
                        </li>
                        <li class="flex items-center gap-4 py-2 relative before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A] before:bottom-1/2 before:top-0 before:left-[0.4rem] before:absolute">
                            <span class="relative py-1 bg-white dark:bg-[#161615]">
                                <span class="flex items-center justify-center rounded-full bg-[#FDFDFC] dark:bg-[#161615] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] w-3.5 h-3.5 border dark:border-[#3E3E3A] border-[#e3e3e0]">
                                    <span class="rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A] w-1.5 h-1.5"></span>
                                </span>
                            </span>
                            <span>Gestiona tu tiempo para compensar.</span>
                        </li>

                    </ul>

                </div>

                <div class="bg-white dark:bg-[#161615] relative lg:-ml-px -mb-px lg:mb-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg aspect-[335/376] lg:aspect-auto w-full lg:w-[438px] shrink-0 overflow-hidden flex items-center justify-center" style="z-index: 1;">
                    @guest
                    {{-- Login Form --}}
                    <div class="w-full max-w-sm px-6 py-8" style="position: relative; z-index: 2;">
                        {{-- <img src="{{ asset('images/logoFiscalia2025.png') }}" alt="Logo Fiscalía" class="w-16 h-16 mx-auto mb-4"> --}}
                        <div class="text-center mb-6">

                            {{-- <img src="{{ asset('images/logoHE.png') }}" alt="Logo HE" class="w-20 h-20 mx-auto mb-4"> --}}
                            <h2 class="text-lg font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">Iniciar Sesión</h2>
                            <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">Ingresa tus credenciales</p>
                        </div>

                        <form method="POST" action="{{ route('authenticate') }}" class="space-y-4" style="position: relative; z-index: 3;">
                            @csrf

                            <!-- Username -->
                            <div style="position: relative; z-index: 4;">
                                <label for="username" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                    Usuario
                                </label>
                                <input
                                    id="username"
                                    name="username"
                                    type="text"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="Nombre de usuario"
                                    value="{{ old('username') }}"
                                    class="w-full px-3 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent"
                                    style="position: relative; z-index: 5; pointer-events: auto;"
                                />
                                @error('username')
                                    <p class="mt-1 text-xs text-[#F53003] dark:text-[#FF4433]">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div style="position: relative; z-index: 4;">
                                <label for="password" class="block text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">
                                    Contraseña
                                </label>
                                <input
                                    id="password"
                                    name="password"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Contraseña"
                                    class="w-full px-3 py-2 border border-[#e3e3e0] dark:border-[#3E3E3A] rounded-sm bg-white dark:bg-[#161615] text-[#1b1b18] dark:text-[#EDEDEC] text-sm focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:border-transparent"
                                    style="position: relative; z-index: 5; pointer-events: auto;"
                                />
                                @error('password')
                                    <p class="mt-1 text-xs text-[#F53003] dark:text-[#FF4433]">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Remember Me -->
                            <div class="flex items-center" style="position: relative; z-index: 4;">
                                <input
                                    id="remember"
                                    name="remember"
                                    type="checkbox"
                                    class="w-4 h-4 text-[#F53003] bg-white dark:bg-[#161615] border-[#e3e3e0] dark:border-[#3E3E3A] rounded focus:ring-[#F53003] focus:ring-2"
                                    style="position: relative; z-index: 5; pointer-events: auto;"
                                />
                                <label for="remember" class="ml-2 text-sm text-[#1b1b18] dark:text-[#EDEDEC]">
                                    Recordarme
                                </label>
                            </div>

                            <!-- Session Status -->
                            @if (session('status'))
                                <div class="mt-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded-sm text-sm text-center">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <!-- Submit Button -->
                            <button
                                type="submit"
                                class="w-full bg-[#F53003] hover:bg-[#d42a02] text-white font-medium py-2 px-4 rounded-sm text-sm transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#F53003] focus:ring-offset-2"
                                style="position: relative; z-index: 5; pointer-events: auto;"
                            >
                                Ingresar
                            </button>

                            <!-- Error Messages -->
                            @if ($errors->any())
                                <div class="mt-4 text-center">
                                    @foreach ($errors->all() as $error)
                                        <p class="text-xs text-[#F53003] dark:text-[#FF4433]">{{ $error }}</p>
                                    @endforeach
                                </div>
                            @endif
                        </form>
                    </div>
                    @else
                    {{-- User is logged in - Show logo and welcome message --}}
                    <div class="text-center">
                        {{-- <img src="{{ asset('images/logoHE.png') }}" alt="Logo HE" class="w-32 h-32 mx-auto mb-4"> --}}
                        <h2 class="text-lg font-medium text-[#1b1b18] dark:text-[#EDEDEC] mb-2">¡Bienvenido!</h2>
                        <p class="text-sm text-[#706f6c] dark:text-[#A1A09A] mb-4">{{ Auth::user()->username ?? 'Usuario' }}</p>
                        <a
                            href="{{ route('dashboard') }}"
                            class="inline-block bg-[#F53003] hover:bg-[#d42a02] text-white font-medium py-2 px-4 rounded-sm text-sm transition-colors duration-200"
                        >
                            Ir al Dashboard
                        </a>
                    </div>
                    @endguest

                    <div class="absolute inset-0 rounded-t-lg lg:rounded-t-none lg:rounded-r-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]" style="pointer-events: none; z-index: 0;"></div>
                </div>
            </main>
        </div>

        @if (Route::has('login'))
            <div class="hidden fixed top-0 right-0 px-6 py-4 sm:block">
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm text-gray-700 underline">Dashboard</a>
                    <a class="text-sm text-blue-700 underline ml-4">Menú Principal</a>
                @endauth
            </div>
        @endif



    </body>
</html>
