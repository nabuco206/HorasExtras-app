{{-- filepath: resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Sistema')</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <nav>
        <a href="{{ route('sistema') }}">Menú Principal</a> |
        <a href="{{ route('solicitud-hes.create') }}">Nueva Solicitud</a>
    </nav>
    <hr>
    <div class="container">
        @yield('content')
    </div>
</body>
</html>