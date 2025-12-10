<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <div id="app">
        <!-- Sidebar -->
        @include('components.layouts.app.sidebar')

        <!-- Main Content -->
        <div class="flex flex-col w-full">
            <!-- Header -->
            @include('components.layouts.app.header')

            <!-- Page Content -->
            <main class="flex-1">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
