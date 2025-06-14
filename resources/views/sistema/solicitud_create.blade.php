{{-- filepath: resources/views/sistema/solicitud_create.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h1 class="mb-4">Nueva Solicitud de Horas Extra</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>¡Ups!</strong> Hay algunos problemas con tu formulario.<br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('solicitud-hes.store') }}">
        @csrf

        <input type="hidden" name="username" value="{{ strstr(auth()->user()->email, '@', true) }}">

        <div class="mb-2">
            <label class="form-label">Tipo de Trabajo</label>
            <select name="id_tipo_trabajo" class="form-control form-control-sm" required>
                <option value="">Seleccione...</option>
                @foreach($tiposTrabajo as $tipo)
                    <option value="{{ $tipo->id }}">{{ $tipo->gls_tipo_trabajo ?? $tipo->nombre ?? $tipo->id }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha</label>
            <input type="date" name="fecha" id="fecha" class="form-control" value="{{ old('fecha') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Hora Inicial</label>
            <input type="time" name="hrs_inicial" id="hrs_inicial" class="form-control" value="{{ old('hrs_inicial') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Hora Final</label>
            <input type="time" name="hrs_final" id="hrs_final" class="form-control" value="{{ old('hrs_final') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">ID Estado</label>
            <input type="number" name="id_estado" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Tipo Solicitud</label>
            <input type="text" name="tipo_solicitud" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Fecha Evento</label>
            <!-- <input type="date" name="fecha_evento" class="form-control" required> -->
            <input type="date" name="fecha_evento" id="fecha_evento" class="form-control" value="{{ old('fecha_evento') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Hora Inicio Evento</label>
            <!-- <input type="time" name="hrs_inicio" class="form-control" required> -->
            <input type="time" name="hrs_inicio" id="hrs_inicio" class="form-control" value="{{ old('hrs_inicio') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Hora Fin Evento</label>
            <!-- <input type="time" name="hrs_fin" class="form-control" required> -->
            <input type="time" name="hrs_fin" id="hrs_fin" class="form-control" value="{{ old('hrs_fin') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">ID Tipo Compensación</label>
            <input type="number" name="id_tipoCompensacion" class="form-control" required>
        </div>

        
        <button type="submit" class="btn btn-primary">Guardar</button>
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function setIfEmpty(id, value) {
            var el = document.getElementById(id);
            if (el && !el.value) el.value = value;
        }
        let today = new Date();
        let yyyy = today.getFullYear();
        let mm = String(today.getMonth() + 1).padStart(2, '0');
        let dd = String(today.getDate()).padStart(2, '0');
        let fechaActual = `${yyyy}-${mm}-${dd}`;
        let hh = String(today.getHours()).padStart(2, '0');
        let min = String(today.getMinutes()).padStart(2, '0');
        let horaActual = `${hh}:${min}`;
        setIfEmpty('fecha', fechaActual);
        setIfEmpty('hrs_inicial', horaActual);
        setIfEmpty('hrs_final', horaActual);
        setIfEmpty('fecha_evento', fechaActual);
        setIfEmpty('hrs_inicio', horaActual);
        setIfEmpty('hrs_fin', horaActual);
    });
</script>
@endsection