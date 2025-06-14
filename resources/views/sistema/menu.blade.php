{{-- filepath: resources/views/sistema/menu.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>Menú Principal</h1>
    <ul>
        <li><a href="{{ route('solicitud-hes.create') }}">Nueva Solicitud de Horas Extra</a></li>
        {{-- Agrega más opciones aquí si lo necesitas --}}
    </ul>
@endsection