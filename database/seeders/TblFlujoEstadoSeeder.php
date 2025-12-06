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
        DB::table('tbl_flujos_estados')->truncate();
        $data = [
            // === HE por Compensación ===
            [
                'flujo_id' => $flujos['HE_COMPENSACION'],
                'estado_origen_id' => $estados['INGRESADO'],
                'estado_destino_id' => $estados['APROBADO_JEFE'],
                'rol_autorizado' => 2,
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
                'rol_autorizado' => 2,
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
                'estado_destino_id' => $estados['APROBADO_JD_D'],
                'rol_autorizado' => 2,
                'condicion_sql' => null,
                'orden' => 1,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'flujo_id' => $flujos['HE_DINERO'],
                'estado_origen_id' => $estados['APROBADO_JD_D'],
                'estado_destino_id' => $estados['APROBADO_UDP_D'],
                'rol_autorizado' => 2,
                'condicion_sql' => null,
                'orden' => 2,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'flujo_id' => $flujos['HE_DINERO'],
                'estado_origen_id' => $estados['APROBADO_UDP_D'],
                'estado_destino_id' => $estados['APROBADO_JUDP_D'],
                'rol_autorizado' => 2,
                'condicion_sql' => null, // ✅ forzado texto
                'orden' => 3,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
             [
                'flujo_id' => $flujos['HE_DINERO'],
                'estado_origen_id' => $estados['APROBADO_JUDP_D'],
                'estado_destino_id' => $estados['APROBADO_DER_D'],
                'rol_autorizado' => 2,
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
                'estado_destino_id' => $estados['COMPENSACION_APROBADA_JEFE'],
                'rol_autorizado' => 2,
                'condicion_sql' => null,
                'orden' => 1,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // [
            //     'flujo_id' => $flujos['SOLICITUD_COMPENSACION'],
            //     'estado_origen_id' => $estados['COMPENSACION_APROBADA_JEFE'],
            //     'estado_destino_id' => $estados['APROBADO_RRHH'],
            //     'rol_autorizado' => 2,
            //     'condicion_sql' => null,
            //     'orden' => 2,
            //     'activo' => true,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ],
        ];

        // Inserción segura (una por una) para SQLite
        $insertedIds = [];
        foreach ($data as $item) {
            $insertedIds[] = DB::table('tbl_flujos_estados')->insertGetId($item);
        }

        // Si existe la tabla pivot, poblarla con los rol_autorizado correspondientes
        try {
            if (DB::getSchemaBuilder()->hasTable('tbl_flujos_estados_roles')) {
                foreach ($data as $index => $item) {
                    $rol = $item['rol_autorizado'] ?? null;
                    $feId = $insertedIds[$index] ?? null;
                    if ($rol !== null && $feId) {
                        // roles a insertar: siempre el rol original
                        $rolesToInsert = [$rol];

                        // Si el rol original es JD (2), añadir UDP/JUDP/DER (3,4,5)
                        if ((int)$rol === 2) {
                            $rolesToInsert = array_unique(array_merge($rolesToInsert, [3,4,5]));
                        }

                        foreach ($rolesToInsert as $r) {
                            // evitar duplicados
                            $exists = DB::table('tbl_flujos_estados_roles')
                                ->where('flujo_estado_id', $feId)
                                ->where('rol_id', $r)
                                ->exists();
                            if (!$exists) {
                                DB::table('tbl_flujos_estados_roles')->insert([
                                    'flujo_estado_id' => $feId,
                                    'rol_id' => $r,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // No bloquear el seeder si por alguna razón la tabla no existe o hay problemas en sqlite
            $this->command->warn('No se pudo poblar la tabla pivot tbl_flujos_estados_roles: ' . $e->getMessage());
        }

        $this->command->info('✅ Flujos de estados insertados correctamente.');
    }
}
