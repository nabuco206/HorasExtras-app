<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblSolicitudHe;

class SolicitudHeController extends Controller
{
    public function create()
    {
        return view('sistema.solicitud_create');
    }

    public function store(Request $request)
    {
        // Valida y guarda la solicitud
        $request->validate([
            'tipo_trabajo' => 'required|integer',
            'fecha' => 'required|date',
            'hrs_inicial' => 'required',
            'hrs_final' => 'required',
            'id_estado' => 'required|integer',
            'tipo_solicitud' => 'required',
            'fecha_evento' => 'required|date',
            'hrs_inicio' => 'required',
            'hrs_fin' => 'required',
            'id_tipoCompensacion' => 'required|integer',
            'min_25' => 'required|integer',
            'min_50' => 'required|integer',
            'total_min' => 'required|integer',
        ]);

        TblSolicitudHe::create($request->all());

        return redirect()->route('sistema')->with('success', 'Solicitud creada correctamente');
    }
}
