<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TblFlujoEstadoSeeder extends Seeder
{
    public function run(): void
    {
        $estados = DB::table('tbl_estados')->pluck('id', 'codigo')->toArray();
        $flujos = DB::table('tbl_flujos')->pluck('id', 'codigo')->toArray();

        $data = [
            // === HE por Compensación ===
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

            // === HE por Dinero ===
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
                'rol_autorizado' => 'JEFE',
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
                'rol_autorizado' => 'JEFE',
                'condicion_sql' => null, // ✅ forzado texto
                'orden' => 3,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // === Solicitud de Compensación ===
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
                'rol_autorizado' => 'JEFE',
                'condicion_sql' => null,
                'orden' => 2,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Inserción segura (una por una) para SQLite
        foreach ($data as $item) {
            DB::table('tbl_flujos_estados')->insert($item);
        }

        $this->command->info('✅ Flujos de estados insertados correctamente.');
    }
}
