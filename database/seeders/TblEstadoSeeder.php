<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TblEstadoSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener los IDs de los flujos por código
        $heCompensacionId = DB::table('tbl_flujos')->where('codigo', 'HE_COMPENSACION')->value('id');
        $heDineroId = DB::table('tbl_flujos')->where('codigo', 'HE_DINERO')->value('id');

        DB::table('tbl_estados')->insert([
            [
                'codigo' => 'INGRESADO',
                'descripcion' => 'Solicitud ingresada por el usuario',
                'tipo_accion' => 'NINGUNA',
                'flujo' => 'AMBOS',
                'flujo_id' => null,
                'es_final' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'APROBADO_JD_D',
                'descripcion' => 'Aprobado Comp. Pago por el jefe directo',
                'tipo_accion' => 'NINGUNA',
                'flujo' => 'HE_DINERO',
                'flujo_id' => $heDineroId,
                'es_final' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'APROBADO_UDP_D',
                'descripcion' => 'Aprobado por UDP Comp. Pago',
                'tipo_accion' => 'NINGUNA',
                'flujo' => 'HE_DINERO',
                'flujo_id' => $heDineroId,
                'es_final' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'APROBADO_JUDP_D',
                'descripcion' => 'Aprobado por JUDP',
                'tipo_accion' => 'NINGUNA',
                'flujo' => 'HE_DINERO',
                'flujo_id' => $heDineroId,
                'es_final' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'APROBADO_DER_D',
                'descripcion' => 'Aprobado por DER Comp. Pago',
                'tipo_accion' => 'SUMA',
                'flujo' => 'HE_DINERO',
                'flujo_id' => $heDineroId,
                'es_final' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'APROBADO_JEFE',
                'descripcion' => 'Aprobado por el jefe - Tiempo disponible en bolsón',
                'tipo_accion' => 'SUMA',
                'flujo' => 'HE_COMPENSACION',
                'flujo_id' => $heCompensacionId,
                'es_final' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'RECHAZADO_JEFE',
                'descripcion' => 'Rechazado por el jefe directo',
                'tipo_accion' => 'NINGUNA',
                'flujo' => 'AMBOS',
                'flujo_id' => null,
                'es_final' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'RECHAZADO_RRHH',
                'descripcion' => 'Rechazado por UDP',
                'tipo_accion' => 'NINGUNA',
                'flujo' => 'AMBOS',
                'flujo_id' => null,
                'es_final' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Estados específicos para compensaciones
            [
                'codigo' => 'COMPENSACION_SOLICITADA',
                'descripcion' => 'Compensación solicitada - Tiempo descontado del bolsón',
                'tipo_accion' => 'RESTA',
                'flujo' => 'HE_COMPENSACION',
                'flujo_id' => $heCompensacionId,
                'es_final' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'COMPENSACION_APROBADA_JEFE',
                'descripcion' => 'Compensación aprobada por jefe - Ciclo completado',
                'tipo_accion' => 'NINGUNA',
                'flujo' => 'HE_COMPENSACION',
                'flujo_id' => $heCompensacionId,
                'es_final' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'COMPENSACION_RECHAZADA_JEFE',
                'descripcion' => 'Compensación rechazada por jefe - Tiempo devuelto al bolsón',
                'tipo_accion' => 'SUMA',
                'flujo' => 'HE_COMPENSACION',
                'flujo_id' => $heCompensacionId,
                'es_final' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
