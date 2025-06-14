{{-- filepath: resources/views/sistema/menu.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1 class="h4 mb-3">Menú Principal</h1>
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

            <form method="POST" action="{{ route('solicitud-hes.store') }}">
                @csrf
                <input type="hidden" name="username" value="{{ strstr(auth()->user()->email, '@', true) }}">
                <input type="hidden" name="tipo_solicitud" value="1">

                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Tipo de Trabajo</label>
                        <select name="id_tipo_trabajo" class="form-control form-control-sm" required>
                            <option value="">Seleccione...</option>
                            @foreach($tiposTrabajo as $tipo)
                                <option value="{{ $tipo->id }}">{{ $tipo->gls_tipo_trabajo ?? $tipo->nombre ?? $tipo->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="fecha" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">ID Tipo Compensación</label>
                        <input type="number" name="id_tipoCompensacion" class="form-control form-control-sm" required value="0">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Hora Inicial</label>
                        <input type="time" name="hrs_inicial" class="form-control form-control-sm" required>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Hora Final</label>
                        <input type="time" name="hrs_final" class="form-control form-control-sm" required>
                    </div>
                    <!-- <div class="col-md-3 mb-2">
                        <label class="form-label">ID Estado</label>
                        <input type="number" name="id_estado" class="form-control form-control-sm" required>
                    </div> 
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Fecha Evento</label>
                        <input type="date" name="fecha_evento" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Hora Inicio Evento</label>
                        <input type="time" name="hrs_inicio" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Hora Fin Evento</label>
                        <input type="time" name="hrs_fin" class="form-control form-control-sm">
                    </div>-->
                    
                    <!-- <div class="col-md-3 mb-2">
                        <label class="form-label">Minutos 25%</label>
                        <input type="number" name="min_25" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Minutos 50%</label>
                        <input type="number" name="min_50" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="form-label">Total Minutos</label>
                        <input type="number" name="total_min" class="form-control form-control-sm">
                    </div> -->
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
                <th>Tipo Trabajo</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Min 25%</th>
                <th>Min 50%</th>
                <th>Total Min</th>
                <th>Creado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($solicitudes as $solicitud)
                <tr>
                    <td>{{ $solicitud->id }}</td>
                    <td>{{ $solicitud->username }}</td>
                    <td>{{ $solicitud->id_tipo_trabajo }}</td>
                    <td>{{ $solicitud->fecha }}</td>
                    <td>{{ $solicitud->id_estado }}</td>
                    <td>{{ $solicitud->min_25 }}</td>
                    <td>{{ $solicitud->min_50 }}</td>
                    <td>{{ $solicitud->total_min }}</td>
                    <td>{{ $solicitud->created_at }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center">No hay solicitudes ingresadas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection