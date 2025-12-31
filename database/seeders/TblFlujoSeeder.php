<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TblFlujoSeeder extends Seeder
{
    public function run(): void
    {
        $flujos = [
               [
                'codigo' => 'AMBOS',
                'descripcion' => '',
                'activo' => true,
            ],
            [
                'codigo' => 'HE_COMPENSACION',
                'descripcion' => 'Aprobación de Horas Extras por compensación de tiempo',
                'activo' => true,
            ],
            [
                'codigo' => 'HE_DINERO',
                'descripcion' => 'Aprobación de Horas Extras con pago en dinero',
                'activo' => true,
            ],
            [
                'codigo' => 'SOLICITUD_COMPENSACION',
                'descripcion' => 'Solicitud de compensación de horas desde el bolsón',
                'activo' => true,
            ],
        ];

        foreach ($flujos as $flujo) {
            DB::table('tbl_flujos')->updateOrInsert(
                ['codigo' => $flujo['codigo']],
                array_merge($flujo, [
                    'updated_at' => now(),
                    'created_at' => now(),
                ])
            );
        }

        $this->command->info('✅ Flujos de trabajo sincronizados correctamente');
    }
}
