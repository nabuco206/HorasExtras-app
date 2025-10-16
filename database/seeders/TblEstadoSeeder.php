<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TblEstadoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tbl_estados')->insert([
            [
                'codigo' => 'INGRESADO',
                'descripcion' => 'Solicitud ingresada por el usuario',
                'tipo_accion' => 'NINGUNA',
                'flujo' => 'AMBOS',
                'es_final' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'APROBADO_JEFE',
                'descripcion' => 'Aprobado por el jefe directo',
                'tipo_accion' => 'NINGUNA',
                'flujo' => 'AMBOS',
                'es_final' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'RECHAZADO_JEFE',
                'descripcion' => 'Rechazado por el jefe directo',
                'tipo_accion' => 'NINGUNA',
                'flujo' => 'AMBOS',
                'es_final' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'APROBADO_RRHH',
                'descripcion' => 'Aprobado por la Unidad de Personal',
                'tipo_accion' => 'SUMA',
                'flujo' => 'AMBOS',
                'es_final' => false,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'RECHAZADO_RRHH',
                'descripcion' => 'Rechazado por la Unidad de Personal',
                'tipo_accion' => 'NINGUNA',
                'flujo' => 'AMBOS',
                'es_final' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'APROBADO_DER',
                'descripcion' => 'Aprobado por el Director Ejecutivo Regional',
                'tipo_accion' => 'SUMA',
                'flujo' => 'AMBOS',
                'es_final' => true,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
