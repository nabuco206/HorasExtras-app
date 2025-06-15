<?php

namespace App\Services;

use App\Models\TblFeriado;
use Carbon\Carbon;

class SolicitudHeService
{
    public function calculaPorcentaje($fecha, $horaInicio, $horaFin, $id_turno = 0)
    {
        echo "Fecha: $fecha, Hora Inicio: $horaInicio, Hora Fin: $horaFin\n";

        $fechaObj = Carbon::parse($fecha);
        $horaInicioObj = Carbon::createFromFormat('H:i', $horaInicio);
        $horaFinObj = Carbon::createFromFormat('H:i', $horaFin);

        $diferenciaMin = $horaInicioObj->diffInMinutes($horaFinObj);
        echo "Diferencia en minutos: $diferenciaMin\n";

        $mmdd = $fechaObj->format('m-d');
        $esFeriado = TblFeriado::where('fecha', $mmdd)->exists();
        $diaSemana = $fechaObj->dayOfWeekIso; // 1 = Lunes, 7 = Domingo

        $min_25 = 0;
        $min_50 = 0;

        if ($id_turno == 0) {
            // Feriado o fin de semana: todo al 50%
            if ($esFeriado || in_array($diaSemana, [6, 7])) {
                $min_50 = $diferenciaMin * 0.5;
                echo "Feriado o fin de semana: Minutos al 50%: $min_50\n";
            } else {
                $inicio = $horaInicioObj->hour * 60 + $horaInicioObj->minute;
                $fin = $horaFinObj->hour * 60 + $horaFinObj->minute;

                $inicio_25 = 18 * 60; // 18:00
                $fin_25 = 21 * 60;    // 21:00

                // Minutos al 25% (entre 18:00 y 21:00)
                if ($inicio < $fin_25 && $fin > $inicio_25) {
                    $min_25 = max(0, min($fin, $fin_25) - max($inicio, $inicio_25));
                    $min_25 *= 0.25;
                }

                // Minutos al 50% después de las 21:00
                if ($fin > $fin_25) {
                    $min_50 = max(0, $fin - max($inicio, $fin_25));
                    $min_50 *= 0.5;
                }

                // Minutos al 50% antes de las 18:00
                if ($inicio < $inicio_25) {
                    $min_50 += max(0, min($fin, $inicio_25) - $inicio);
                }
            }
        }

        $total_min = $diferenciaMin + $min_25 + $min_50;

        echo "Minutos al 25%: $min_25, Minutos al 50%: $min_50, el total: $total_min\n";

        return [
            'min_reales' => $diferenciaMin,
            'min_25' => $min_25,
            'min_50' => $min_50,
            'total_min' => $total_min,
        ];
    }
}