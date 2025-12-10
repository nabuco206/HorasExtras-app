<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblSolicitudCompensa;

class CompensacionController extends Controller
{
    public function todasCompensaciones()
    {
        $user = auth()->user();

        // Filtrar solicitudes según el rol del usuario
        if ($user->id_rol === 2) {
            $solicitudes = TblSolicitudCompensa::where('cod_fiscalia', $user->cod_fiscalia)->get();
        } elseif ($user->id_rol === 3) {
            $solicitudes = TblSolicitudCompensa::all();
        } else {
            return redirect()->route('dashboard')->with('error', 'No tiene permiso para acceder a esta página.');
        }

        return view('livewire.sistema.aprobaciones-compensacion', compact('solicitudes'));
    }
}