<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TblSolicitudCompensa;
use App\Models\TblPersona;
use App\Models\TblBolsonTiempo;
use Carbon\Carbon;

class TblSolicitudCompensaSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🔄 Creando solicitudes de compensación de prueba...');

        // Obtener usuarios que tengan bolsones de tiempo con saldo disponible
        $usuariosConSaldo = TblBolsonTiempo::where('saldo_min', '>', 240)
            ->where('estado', 'DISPONIBLE')
            ->where('activo', true)
            ->distinct()
            ->pluck('username')
            ->take(8);

        if ($usuariosConSaldo->isEmpty()) {
            $this->command->warn('   ⚠️  No hay usuarios con saldo en bolsón para crear compensaciones');
            return;
        }

        $solicitudesCreadas = 0;

        foreach ($usuariosConSaldo as $username) {
            // Obtener datos de la persona
            $persona = TblPersona::where('username', $username)->first();
            if (!$persona) continue;

            // Obtener saldo disponible
            $saldoTotal = TblBolsonTiempo::where('username', $username)
                ->where('estado', 'DISPONIBLE')
                ->where('activo', true)
                ->sum('saldo_min');

            if ($saldoTotal < 240) continue; // Al menos 4 horas disponibles

            // Crear 1-2 solicitudes por usuario
            $numSolicitudes = rand(1, 2);

            for ($i = 0; $i < $numSolicitudes; $i++) {
                $minutosASolicitar = rand(120, min(480, floor($saldoTotal / 2))); // Entre 2-8 horas, máximo la mitad del saldo
                $fechaCompensacion = Carbon::now()->addDays(rand(1, 15)); // Entre 1-15 días en el futuro

                // Calcular horas de compensación (simulando un día de trabajo)
                $horasIniciales = ['09:00', '10:00', '11:00'];
                $horaInicial = $horasIniciales[array_rand($horasIniciales)];
                $horaFinal = Carbon::parse($horaInicial)->addMinutes($minutosASolicitar)->format('H:i');

                // Crear la solicitud en estado COMPENSACION_SOLICITADA (8)
                $solicitud = TblSolicitudCompensa::create([
                    'username' => $username,
                    'cod_fiscalia' => $persona->cod_fiscalia ?? '001',
                    'fecha_solicitud' => $fechaCompensacion->toDateString(),
                    'hrs_inicial' => $horaInicial,
                    'hrs_final' => $horaFinal,
                    'minutos_solicitados' => $minutosASolicitar,
                    'observaciones' => "Solicitud de compensación de prueba - {$minutosASolicitar} minutos",
                    'id_estado' => 8, // COMPENSACION_SOLICITADA
                    'created_at' => Carbon::now()->subDays(rand(1, 5)), // Creada hace 1-5 días
                    'updated_at' => Carbon::now()->subDays(rand(1, 5)),
                ]);

                // Simular el descuento del bolsón que se haría al crear la solicitud
                $bolsonDelUsuario = TblBolsonTiempo::where('username', $username)
                    ->where('saldo_min', '>=', $minutosASolicitar)
                    ->where('estado', 'DISPONIBLE')
                    ->where('activo', true)
                    ->first();

                if ($bolsonDelUsuario) {
                    $bolsonDelUsuario->saldo_min -= $minutosASolicitar;
                    $bolsonDelUsuario->save();
                    $saldoTotal -= $minutosASolicitar;
                }

                $solicitudesCreadas++;

                $this->command->info("   ✅ Compensación #{$solicitud->id}: {$username} - {$minutosASolicitar} min - {$fechaCompensacion->format('d/m/Y')}");
            }
        }

        // Crear algunas solicitudes ya aprobadas para estadísticas
        $usuariosParaAprobadas = $usuariosConSaldo->take(3);

        foreach ($usuariosParaAprobadas as $username) {
            $persona = TblPersona::where('username', $username)->first();
            if (!$persona) continue;

            $minutosAprobados = rand(120, 360);
            $fechaPasada = Carbon::now()->subDays(rand(5, 15));

            // Calcular horarios
            $horaInicial = '09:00';
            $horaFinal = Carbon::parse($horaInicial)->addMinutes($minutosAprobados)->format('H:i');

            $solicitudAprobada = TblSolicitudCompensa::create([
                'username' => $username,
                'cod_fiscalia' => $persona->cod_fiscalia ?? '001',
                'fecha_solicitud' => $fechaPasada->toDateString(),
                'hrs_inicial' => $horaInicial,
                'hrs_final' => $horaFinal,
                'minutos_solicitados' => $minutosAprobados,
                'minutos_aprobados' => $minutosAprobados,
                'observaciones' => "Compensación aprobada de prueba - {$minutosAprobados} minutos",
                'id_estado' => 9, // COMPENSACION_APROBADA_JEFE
                'aprobado_por' => 'SISTEMA',
                'fecha_aprobacion' => $fechaPasada->addDays(1),
                'created_at' => $fechaPasada,
                'updated_at' => $fechaPasada->addDays(1),
            ]);

            $solicitudesCreadas++;
            $this->command->info("   ✅ Compensación aprobada #{$solicitudAprobada->id}: {$username} - {$minutosAprobados} min");
        }

        // Crear algunas rechazadas
        $usuariosParaRechazadas = $usuariosConSaldo->take(2);

        foreach ($usuariosParaRechazadas as $username) {
            $persona = TblPersona::where('username', $username)->first();
            if (!$persona) continue;

            $minutosRechazados = rand(120, 240);
            $fechaPasada = Carbon::now()->subDays(rand(3, 10));

            // Calcular horarios
            $horaInicial = '10:00';
            $horaFinal = Carbon::parse($horaInicial)->addMinutes($minutosRechazados)->format('H:i');

            $solicitudRechazada = TblSolicitudCompensa::create([
                'username' => $username,
                'cod_fiscalia' => $persona->cod_fiscalia ?? '001',
                'fecha_solicitud' => $fechaPasada->toDateString(),
                'hrs_inicial' => $horaInicial,
                'hrs_final' => $horaFinal,
                'minutos_solicitados' => $minutosRechazados,
                'observaciones' => "Compensación rechazada de prueba - {$minutosRechazados} minutos | RECHAZO: No cumple requisitos",
                'id_estado' => 10, // COMPENSACION_RECHAZADA_JEFE
                'aprobado_por' => 'SISTEMA',
                'fecha_aprobacion' => $fechaPasada->addDays(1),
                'created_at' => $fechaPasada,
                'updated_at' => $fechaPasada->addDays(1),
            ]);

            $solicitudesCreadas++;
            $this->command->info("   ❌ Compensación rechazada #{$solicitudRechazada->id}: {$username} - {$minutosRechazados} min");
        }

        $this->command->info("✅ Creadas {$solicitudesCreadas} solicitudes de compensación");
        $this->command->info('   • Pendientes: ' . TblSolicitudCompensa::where('id_estado', 8)->count());
        $this->command->info('   • Aprobadas: ' . TblSolicitudCompensa::where('id_estado', 9)->count());
        $this->command->info('   • Rechazadas: ' . TblSolicitudCompensa::where('id_estado', 10)->count());
    }
}
