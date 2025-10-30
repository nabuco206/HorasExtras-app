<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TblFlujoEstadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mapeo de códigos de estado a IDs (basado en TblEstadoSeeder)
        $estados = [
            'INGRESADO' => 1,
            'APROBADO_LIDER' => 2,
            'APROBADO_JEFE' => 3,
            'RECHAZADO_JEFE' => 4,
            'APROBADO_RRHH' => 5,
            'RECHAZADO_RRHH' => 6,
            'APROBADO_DER' => 7,
        ];

        // Mapeo de códigos de flujo a IDs (basado en TblFlujoSeeder)
        $flujos = [
            'HE_COMPENSACION' => 1,
            'HE_DINERO' => 2,
            'SOLICITUD_COMPENSACION' => 3,
        ];

        DB::table('tbl_flujos_estados')->insert([
            // === FLUJO SIMPLE: HE por Compensación (Solo 2 estados) ===
            // INGRESADO -> APROBADO_JEFE (aprobación simple, tiempo va al bolsón)
            [
                'flujo_id' => $flujos['HE_COMPENSACION'],
                'estado_origen_id' => $estados['INGRESADO'],
                'estado_destino_id' => $estados['APROBADO_JEFE'],
                'rol_autorizado' => 'JEFE',
                'condicion_sql' => null,
                'orden' => 1,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // INGRESADO -> RECHAZADO_JEFE (rechazo por el jefe)
            [
                'flujo_id' => $flujos['HE_COMPENSACION'],
                'estado_origen_id' => $estados['INGRESADO'],
                'estado_destino_id' => $estados['RECHAZADO_JEFE'],
                'rol_autorizado' => 'JEFE',
                'condicion_sql' => null,
                'orden' => 2,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === FLUJOS COMPLEJOS (mantenemos los otros para comparación) ===
            // HE con Pago - Flujo completo
            [
                'flujo_id' => $flujos['HE_DINERO'],
                'estado_origen_id' => $estados['INGRESADO'],
                'estado_destino_id' => $estados['APROBADO_LIDER'],
                'rol_autorizado' => 'JEFE',
                'condicion_sql' => null,
                'orden' => 1,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'flujo_id' => $flujos['HE_DINERO'],
                'estado_origen_id' => $estados['APROBADO_LIDER'],
                'estado_destino_id' => $estados['APROBADO_RRHH'],
                'rol_autorizado' => 'UPER',
                'condicion_sql' => null,
                'orden' => 2,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'flujo_id' => $flujos['HE_DINERO'],
                'estado_origen_id' => $estados['APROBADO_RRHH'],
                'estado_destino_id' => $estados['APROBADO_DER'],
                'rol_autorizado' => 'DIRECCION',
                'condicion_sql' => 'total_minutos > 240',
                'orden' => 3,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Solicitud de compensación
            [
                'flujo_id' => $flujos['SOLICITUD_COMPENSACION'],
                'estado_origen_id' => $estados['INGRESADO'],
                'estado_destino_id' => $estados['APROBADO_LIDER'],
                'rol_autorizado' => 'JEFE',
                'condicion_sql' => null,
                'orden' => 1,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'flujo_id' => $flujos['SOLICITUD_COMPENSACION'],
                'estado_origen_id' => $estados['APROBADO_LIDER'],
                'estado_destino_id' => $estados['APROBADO_RRHH'],
                'rol_autorizado' => 'UPER',
                'condicion_sql' => null,
                'orden' => 2,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Rechazos para otros flujos
            [
                'flujo_id' => $flujos['HE_DINERO'],
                'estado_origen_id' => $estados['INGRESADO'],
                'estado_destino_id' => $estados['RECHAZADO_JEFE'],
                'rol_autorizado' => 'JEFE',
                'condicion_sql' => null,
                'orden' => 10,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'flujo_id' => $flujos['SOLICITUD_COMPENSACION'],
                'estado_origen_id' => $estados['INGRESADO'],
                'estado_destino_id' => $estados['RECHAZADO_JEFE'],
                'rol_autorizado' => 'JEFE',
                'condicion_sql' => null,
                'orden' => 10,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('✅ Flujos de estados configurados correctamente');
        $this->command->info('   • HE_COMPENSACION (SIMPLE): INGRESADO → APROBADO_JEFE (tiempo al bolsón)');
        $this->command->info('   • HE_DINERO: INGRESADO → APROBADO_LIDER → APROBADO_RRHH → APROBADO_DER');
        $this->command->info('   • SOLICITUD_COMPENSACION: INGRESADO → APROBADO_LIDER → APROBADO_RRHH');
        $this->command->info('   • Flujo simple de HE_COMPENSACION listo para pruebas');
    }
}
