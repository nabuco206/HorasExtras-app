<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TblFlujoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tbl_flujos')->insert([
            [
                'codigo' => 'HE_COMPENSACION',
                'descripcion' => 'Aprobación de Horas Extras por compensación de tiempo',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'HE_DINERO',
                'descripcion' => 'Aprobación de Horas Extras con pago en dinero',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'codigo' => 'SOLICITUD_COMPENSACION',
                'descripcion' => 'Solicitud de compensación de horas desde el bolsón',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('✅ Flujos de trabajo creados correctamente');
        $this->command->info('   • HE_COMPENSACION - HE por compensación');
        $this->command->info('   • HE_DINERO - HE con pago');
        $this->command->info('   • SOLICITUD_COMPENSACION - Compensación desde bolsón');
    }
}
