{{-- filepath: resources/views/sistema/solicitud_create.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Nueva Solicitud de Horas Extra</h1>
    <form method="POST" action="{{ route('solicitud-hes.store') }}">
        @csrf
        <label>Tipo de Trabajo:</label>
        <input type="number" name="tipo_trabajo" required><br>

        <label>Fecha:</label>
        <input type="date" name="fecha" required><br>

        <label>Hora Inicial:</label>
        <input type="time" name="hrs_inicial" required><br>

        <label>Hora Final:</label>
        <input type="time" name="hrs_final" required><br>

        <label>ID Estado:</label>
        <input type="number" name="id_estado" required><br>

        <label>Tipo Solicitud:</label>
        <input type="text" name="tipo_solicitud" required><br>

        <label>Fecha Evento:</label>
        <input type="date" name="fecha_evento" required><br>

        <label>Hora Inicio Evento:</label>
        <input type="time" name="hrs_inicio" required><br>

        <label>Hora Fin Evento:</label>
        <input type="time" name="hrs_fin" required><br>

        <label>ID Tipo Compensación:</label>
        <input type="number" name="id_tipoCompensacion" required><br>

        <label>Minutos 25%:</label>
        <input type="number" name="min_25" required><br>

        <label>Minutos 50%:</label>
        <input type="number" name="min_50" required><br>

        <label>Total Minutos:</label>
        <input type="number" name="total_min" required><br>

        <button type="submit">Guardar</button>
    </form>
@endsection