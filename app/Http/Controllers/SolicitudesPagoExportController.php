<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TblSolicitudCompensa;

class SolicitudesPagoExportController extends Controller
{
    public function export(Request $request)
    {
        $user = Auth::user();

        $query = TblSolicitudCompensa::query();
        $query->whereIn('id_estado', [10,11]);

        if ($user && intval($user->role_id) === 2) {
            $cod = $user->cod_fiscalia ?? null;
            if ($cod) $query->where('cod_fiscalia', $cod);
        }

        if ($request->filled('estadoId')) {
            $query->where('id_estado', $request->input('estadoId'));
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function($q) use ($s) {
                $q->where('username', 'like', "%$s%")
                  ->orWhere('observaciones', 'like', "%$s%");
            });
        }

        $rows = $query->orderBy('created_at','desc')->get();

        $filename = 'solicitudes_pago_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            // header
            fputcsv($out, ['id','username','cod_fiscalia','fecha_solicitud','hrs_inicial','hrs_final','minutos_solicitados','minutos_aprobados','id_estado','observaciones']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->username,
                    $r->cod_fiscalia,
                    $r->fecha_solicitud,
                    $r->hrs_inicial,
                    $r->hrs_final,
                    $r->minutos_solicitados,
                    $r->minutos_aprobados,
                    $r->id_estado,
                    str_replace("\n", ' ', $r->observaciones),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
