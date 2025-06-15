<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TblSolicitudHe;
use App\Models\TblTipoTrabajo;
use App\Models\TblFeriado;
use Carbon\Carbon;
use App\Services\SolicitudHeService;

class SolicitudHeController extends Controller
{
    public function create()
    {
        $tiposTrabajo = TblTipoTrabajo::all();
        return view('sistema.solicitud_create', compact('tiposTrabajo'));
    }

    public function store(Request $request, SolicitudHeService $servicio)
    {
        // 1. Validación básica de los campos
        $request->validate([
            'id_tipo_trabajo' => 'required|integer',
            'fecha' => 'required|date|before_or_equal:today', // No permite fechas futuras
            'hrs_inicial' => 'required|date_format:H:i',
            'hrs_final' => 'required|date_format:H:i|after:hrs_inicial', // Final debe ser después de inicial
            'tipo_solicitud' => 'required',
            'id_tipoCompensacion' => 'required|in:0,1',
        ]);
        // 2. Lógica de restricción por turno (inserta aquí)
        $persona = auth()->user()->persona; // Ajusta según tu relación
        if (!$persona) {
            return back()->withErrors(['El usuario no tiene persona asociada.']);
        }
        if ($persona->id_turno != 0) {
            $turno = \App\Models\TblTurno::find($persona->id_turno);
            $diaSemana = \Carbon\Carbon::parse($request->fecha)->dayOfWeekIso; // 1=Lunes, 7=Domingo
            $diasTurno = explode(',', $turno->dias);

            if (in_array($diaSemana, $diasTurno)) {
                if (
                    $request->hrs_inicial >= $turno->hora_inicio &&
                    $request->hrs_final <= $turno->hora_fin
                ) {
                    return back()->withErrors(['No puedes ingresar solicitudes dentro de tu horario laboral.']);
                }
            }
        }

        $resultados = $servicio->calculaPorcentaje(
                                $request->fecha,
                                $request->hrs_inicial,
                                $request->hrs_final,
                                auth()->user()->persona->id_turno
                            );

        

        $data = $request->all();
        $data = array_merge($data, $resultados);
        $data['username'] = strstr(auth()->user()->email, '@', true);

        $solicitud = TblSolicitudHe::create($data);

        return redirect()
                        ->route('sistema')
                        ->with('success',
                            'Solicitud creada correctamente. ID: ' . $solicitud->id .
                            ' | min_25: ' . $solicitud->min_25 .
                            ' | min_50: ' . $solicitud->min_50 .
                            ' | min_reales: ' . $solicitud->min_reales .
                            ' | total_min: ' . $solicitud->total_min
                        );
    }

   
}
