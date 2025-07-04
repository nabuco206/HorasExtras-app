<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('sistema') }}">Sistema HEE</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarMenu">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('sistema') }}">Menú Principal</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="menuDropdown" role="button" data-bs-toggle="dropdown">
                            Dropdown
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="menuDropdown">
                            <li><a class="dropdown-item" href="#">Action</a></li>
                            <li><a class="dropdown-item" href="#">Another action</a></li>
                            <li><hr class="dropdown-divider"></li>
                        </ul>
                    </li>
                    
                    @if(auth()->check() && auth()->user()->rol === 1)
                        <li class="nav-item">
                            <a class="nav-link" href="/admin" target="_blank">Admin Filament</a>
                        </li>
                         <li class="nav-item">
                            <a class="nav-link" href="admin/tbl-personas" target="_blank">Admin Personas</a>
                        </li>
                    @endif
                </ul>

                <!-- Botón de logout -->
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-flex">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mt-4">

<x-layouts.app :title="__('Ingreso Horas Extraordinarias')">
     <!-- <h1 class="h4 mb-3">Menú Principal</h1> -->
    <!-- Botón para mostrar/ocultar el formulario -->
    <button class="btn btn-sm btn-success mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#formSolicitud" aria-expanded="false" aria-controls="formSolicitud">
        Nueva Solicitud
    </button>

    <div class="collapse mb-3 {{ $errors->any() ? 'show' : '' }}" id="formSolicitud">
        <div class="card card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>¡Ups!</strong> Hay problemas con tu formulario:<br>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                </div>
            @endif

            <div id="alerta-error"></div>

            <form method="POST" action="{{ route('solicitud-hes.store') }}" class="mx-auto" style="max-width: 500px;">
                <h5 class="text-center mb-3">Ingreso Trabajo Extraordinario</h5>
                @csrf
                <input type="hidden" name="username" value="{{ strstr(auth()->user()->email, '@', true) }}">
                <input type="hidden" name="tipo_solicitud" value="1">

                <!-- Línea 1: Tipo de Trabajo -->
                <div class="mb-2">
                    <label class="form-label">Tipo de Trabajo</label>
                    <select name="id_tipo_trabajo" class="form-control form-control-sm w-auto" style="min-width: 200px;" required>
                        <option value="">Seleccione...</option>
                        @foreach($tiposTrabajo as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->gls_tipo_trabajo ?? $tipo->nombre ?? $tipo->id }}</option>
                        @endforeach
                    </select>
                </div>
                <!-- Línea 2: Fecha, Hora Inicial y Hora Final -->
                <div class="d-flex gap-2 mb-2">
                    <div>
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control form-control-sm w-auto" style="min-width: 120px;" required>
                    </div>
                    <div>
                        <label class="form-label">Hora Inicial</label>
                        <input type="time" name="hrs_inicial" class="form-control form-control-sm w-auto" style="min-width: 100px;" required>
                    </div>
                    <div>
                        <label class="form-label">Hora Final</label>
                        <input type="time" name="hrs_final" class="form-control form-control-sm w-auto" style="min-width: 100px;" required>
                    </div>
                </div>
                <div id="diferencia-horas" class="small text-primary mb-2"></div>
                <!-- Línea 3: Checkbox Se propone pago -->
                <div class="mb-2">
                    <div class="form-check">
                        <input type="hidden" name="id_tipoCompensacion" value="0">
                        <input class="form-check-input" type="checkbox" name="id_tipoCompensacion" id="id_tipoCompensacion" value="1">
                        <label class="form-check-label" for="id_tipoCompensacion">
                            Se propone pago
                        </label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm mt-2">Guardar</button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <h2 class="h5">Solicitudes Ingresadas</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <!-- <th>Tipo Trabajo</th> -->
                <!-- <th>Estado</th> -->
                <th>Fecha</th>
                <th>Ini</th>
                <th>Fin</th>
                <th>Min Real</th>
                <th>Min 25%</th>
                <th>Min 50%</th>
                <th>Total Min</th>
                <th>Creado</th>
                <th>Compensacion</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($solicitudes as $solicitud)
                <tr>
                    <td>{{ $solicitud->id }}</td>
                    <td>{{ $solicitud->username }}</td>
                    <!-- <td>{{ $solicitud->id_tipo_trabajo }}</td> -->
                    <!-- <td>{{ $solicitud->id_estado }}</td> -->
                    <td>{{ \Carbon\Carbon::parse($solicitud->fecha)->format('d/m/Y') }}</td>
                    <td>{{ $solicitud->hrs_inicial }}</td>
                    <td>{{ $solicitud->hrs_final }}</td>
                    <td>{{ $solicitud->min_reales }}</td>
                    <td>{{ $solicitud->min_25 }}</td>
                    <td>{{ $solicitud->min_50 }}</td>
                    <td>{{ $solicitud->total_min }}</td>
                    <td>{{ \Carbon\Carbon::parse($solicitud->created_at)->format('d/m/Y H:m') }}</td>
                    <td>{{ $solicitud->id_tipoCompensacion }}</td>
                </tr>
            @empty  
                <tr>
                    <td colspan="9" class="text-center">No hay solicitudes ingresadas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</x-layouts.app>
<!-- </section> -->
</div>