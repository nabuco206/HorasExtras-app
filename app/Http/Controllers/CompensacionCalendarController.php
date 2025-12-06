<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TblSolicitudCompensa;
use App\Models\TblEstado;

class CompensacionCalendarController extends Controller
{
    /**
     * Devuelve eventos para FullCalendar filtrados por cod_fiscalia del JD autenticado.
     */
    public function events(Request $request)
    {
        $user = Auth::user();
        $codFiscalia = $user->cod_fiscalia ?? null;

    $query = TblSolicitudCompensa::query();

        if ($codFiscalia) {
            $query->where('cod_fiscalia', $codFiscalia);
        }

        // Opcional: filtrar sólo aprobadas y solicitudes solicitadas según preferencia
        // Por ahora devolveremos estados relevantes: 9 (solicitada), 10/11 (aprobadas) según datos del sistema.

        $solicitudes = $query->get();

    // resolver ids de estados por codigo para evitar hardcodes
    $estadoSolicitadaId = TblEstado::where('codigo', 'COMPENSACION_SOLICITADA')->value('id') ?? 9;
    $estadoAprobadaId = TblEstado::where('codigo', 'COMPENSACION_APROBADA_JEFE')->value('id') ?? 10;
    $estadoRechazadaId = TblEstado::where('codigo', 'COMPENSACION_RECHAZADA_JEFE')->value('id') ?? 11;

        $events = $solicitudes->map(function ($s) use ($estadoSolicitadaId, $estadoAprobadaId, $estadoRechazadaId) {
            // construir start y end con fecha_solicitud + hrs_inicial/hrs_final
            $date = \Carbon\Carbon::parse($s->fecha_solicitud);

            // hrs_inicial/hrs_final pueden ser strings o objetos (Time). Manejar ambos.
            $startStr = $date->toDateString();
            if (!empty($s->hrs_inicial)) {
                $hrsInit = is_object($s->hrs_inicial) ? $s->hrs_inicial->format('H:i') : (string) $s->hrs_inicial;
                $startStr .= ' ' . $hrsInit;
            } else {
                $startStr .= ' 00:00';
            }

            $endStr = $date->toDateString();
            if (!empty($s->hrs_final)) {
                $hrsEnd = is_object($s->hrs_final) ? $s->hrs_final->format('H:i') : (string) $s->hrs_final;
                $endStr .= ' ' . $hrsEnd;
            } else {
                $endStr .= ' 23:59';
            }

            $start = \Carbon\Carbon::parse($startStr);
            $end = \Carbon\Carbon::parse($endStr);

            // mapear color según estado (usar ids resueltos)
            $color = '#3788d8'; // azul por defecto
            $label = $s->estado?->nombre ?? 'Compensación';
            if ($s->id_estado == $estadoSolicitadaId) {
                $color = '#f59e0b'; // naranja para solicitada
            } elseif ($s->id_estado == $estadoAprobadaId) {
                $color = '#10b981'; // verde para aprobada
            } elseif ($s->id_estado == $estadoRechazadaId) {
                $color = '#ef4444'; // rojo para rechazada
            }

            return [
                'id' => $s->id,
                'title' => $s->username . ' - ' . $label,
                'start' => $start->toDateTimeString(),
                'end' => $end->toDateTimeString(),
                'color' => $color,
                'extendedProps' => [
                    'minutos_solicitados' => $s->minutos_solicitados,
                    'minutos_aprobados' => $s->minutos_aprobados,
                    'cod_fiscalia' => $s->cod_fiscalia,
                    'estado' => $s->id_estado,
                ],
            ];
        })->values();

        return response()->json($events);
    }
}
