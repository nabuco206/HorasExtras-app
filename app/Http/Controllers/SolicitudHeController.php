<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblSolicitudHe;
use App\Models\TblTipoTrabajo;

class SolicitudHeController extends Controller
{
    public function create()
    {
        $tiposTrabajo = TblTipoTrabajo::all();
        return view('sistema.solicitud_create', compact('tiposTrabajo'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_tipo_trabajo' => 'required|integer',
            'fecha' => 'required|date',
            'hrs_inicial' => 'required',
            'hrs_final' => 'required',
            'tipo_solicitud' => 'required',
            'id_tipoCompensacion' => 'required|integer',
        ]);

        $data = $request->all();
        $data['username'] = strstr(auth()->user()->email, '@', true);

        $solicitud = TblSolicitudHe::create($data);


        return redirect()
                ->route('sistema')
                ->with('success', 'Solicitud creada correctamente. ID: ' . $solicitud->id);
    }
}
