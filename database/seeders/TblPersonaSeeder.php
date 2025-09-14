<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TblPersona;
use App\Models\TblFiscalia;

class TblPersonaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener fiscalías disponibles
        // $fiscalias = TblFiscalia::pluck('id')->toArray();
        $fiscalias = TblFiscalia::pluck('cod_fiscalia')->toArray();

        // Crear algunos líderes

        TblPersona::create([
            'Nombre' => 'María',
            'Apellido' => 'González',
            'UserName' => 'maria.gonzalez',
            'cod_fiscalia' => 501, // Fiscalia de Valparaiso
            'id_escalafon' => 1,
            'flag_lider' => true,
            'password' => bcrypt('1234'),
            'id_rol' => 0,
        ]);

        TblPersona::create([
            'Nombre' => 'Carlos',
            'Apellido' => 'Rodríguez',
            'UserName' => 'carlos.rodriguez',
            'cod_fiscalia' => 502, // Fiscalia de Viña del Mar
            'id_escalafon' => 1,
            'flag_lider' => true,
            'password' => bcrypt('1234'),
            'id_rol' => 0,
        ]);

        TblPersona::create([
            'Nombre' => 'Andrea',
            'Apellido' => 'Silva',
            'UserName' => 'andrea.silva',
            'cod_fiscalia' => 504, // Fiscalia de Quilpue
            'id_escalafon' => 1,
            'flag_lider' => true,
            'password' => bcrypt('1234'),
            'id_rol' => 0,
        ]);

        // Crear algunas personas regulares
        TblPersona::create([
            'Nombre' => 'Ana',
            'Apellido' => 'López',
            'UserName' => 'ana.lopez',
            'cod_fiscalia' => 501, // Fiscalia de Valparaiso
            'id_escalafon' => 1,
            'flag_lider' => false,
            'password' => bcrypt('1234'),
            'id_rol' => 0,
        ]);

        TblPersona::create([
            'Nombre' => 'Luis',
            'Apellido' => 'Martínez',
            'UserName' => 'luis.martinez',
            'cod_fiscalia' => 507, // Fiscalia de Villa Alemana
            'id_escalafon' => 1,
            'flag_lider' => false,
            'password' => bcrypt('1234'),
            'id_rol' => 0,
        ]);

        TblPersona::create([
            'Nombre' => 'Carmen',
            'Apellido' => 'Morales',
            'UserName' => 'carmen.morales',
            'cod_fiscalia' => 503, // Fiscalia de San Antonio
            'id_escalafon' => 1,
            'flag_lider' => false,
            'password' => bcrypt('1234'),
            'id_rol' => 0,
        ]);

        // Crear usando Factory con fiscalías reales
        TblPersona::factory()->puedeSerLider()->count(3)->create();
        TblPersona::factory()->noPuedeSerLider()->count(5)->create();
    }
}
