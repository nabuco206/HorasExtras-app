<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TblBolsonTiempo;
use App\Models\TblSolicitudHe;
use App\Models\TblPersona;
use Carbon\Carbon;

class TblBolsonTiempoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Creando bolsones de tiempo de prueba...');

        // Obtener solicitudes HE aprobadas para crear bolsones
        $solicitudesAprobadas = TblSolicitudHe::where('id_estado', 3) // APROBADO_JEFE
            ->take(5)
            ->get();

        if ($solicitudesAprobadas->isEmpty()) {
            $this->command->warn('   ⚠️  No hay solicitudes HE aprobadas para crear bolsones');
            return;
        }

        $bolsonesCreados = 0;

        foreach ($solicitudesAprobadas as $solicitud) {
            // Crear un bolsón disponible basado en la HE aprobada
            $fechaVencimiento = Carbon::parse($solicitud->fecha)->addMonths(12); // Vence en 12 meses

            $bolson = TblBolsonTiempo::create([
                'username' => $solicitud->username,
                'id_solicitud_he' => $solicitud->id,
                'fecha_crea' => $solicitud->fecha,
                'minutos' => $solicitud->total_min ?? rand(240, 480),
                'fecha_vence' => $fechaVencimiento,
                'saldo_min' => $solicitud->total_min ?? rand(240, 480), // Saldo disponible inicial
                'origen' => 'HE_APROBADA',
                'activo' => true,
                'estado' => 'DISPONIBLE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $bolsonesCreados++;
            $minutosDisponibles = $bolson->saldo_min;
            $this->command->info("   ✅ Bolsón #{$bolson->id}: {$solicitud->username} - {$minutosDisponibles} min disponibles");
        }

        // Crear algunos bolsones adicionales para usuarios que necesiten más saldo
        $usuariosAdicionales = TblPersona::whereNotIn('username', $solicitudesAprobadas->pluck('username'))
            ->take(3)
            ->get();

        foreach ($usuariosAdicionales as $persona) {
            // Buscar una solicitud HE cualquiera para referenciar (o crear referencia ficticia)
            $solicitudReferencia = TblSolicitudHe::where('username', $persona->username)->first();

            if (!$solicitudReferencia) {
                continue; // Skip si no tiene HE
            }

            $minutosBase = rand(300, 600); // Entre 5-10 horas
            $fechaVencimiento = now()->addMonths(6);

            $bolson = TblBolsonTiempo::create([
                'username' => $persona->username,
                'id_solicitud_he' => $solicitudReferencia->id,
                'fecha_crea' => now()->subDays(rand(10, 30)),
                'minutos' => $minutosBase,
                'fecha_vence' => $fechaVencimiento,
                'saldo_min' => $minutosBase, // Todo disponible
                'origen' => 'HE_APROBADA',
                'activo' => true,
                'estado' => 'DISPONIBLE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $bolsonesCreados++;
            $this->command->info("   ✅ Bolsón adicional #{$bolson->id}: {$persona->username} - {$minutosBase} min disponibles");
        }

        $this->command->info("✅ Creados {$bolsonesCreados} bolsones de tiempo");
        $this->command->info('   • Total minutos disponibles: ' . TblBolsonTiempo::where('estado', 'DISPONIBLE')->sum('saldo_min'));
        $this->command->info('   • Usuarios con bolsones: ' . TblBolsonTiempo::distinct('username')->count());
    }
}
