{{-- filepath: resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Sistema')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    @php
        use Spatie\Menu\Laravel\Menu;
        use Spatie\Menu\Laravel\Link;

        $menu = Menu::new()
            ->add(Link::to(route('sistema'), 'Menú Principal'))
            ->add(Link::to(route('solicitud-hes.create'), 'Nueva Solicitud'))
            ->add(Link::to(route('logout'), 'Cerrar sesión'));

        if(auth()->user()->rol === 1) {
            $menu->add(Link::to('/admin', 'Admin'));
        }
    @endphp

    {!! $menu->addClass('nav')->render() !!}
    <hr>
    <div class="container">
        @yield('content')
    </div>

    <!-- Bootstrap JS (necesario para collapse, modal, etc.) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>