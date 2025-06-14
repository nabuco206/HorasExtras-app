<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblSolicitudHe;
use App\Models\TblTipoTrabajo;

class SistemaController extends Controller
{
    public function menu()
    {
        $solicitudes = TblSolicitudHe::orderBy('created_at', 'desc')->get();
        $tiposTrabajo = TblTipoTrabajo::all();
        return view('sistema.menu', compact('solicitudes', 'tiposTrabajo'));
    }
}
