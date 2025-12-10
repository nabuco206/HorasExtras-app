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

        // Crear usuario del sistema
        TblPersona::create([
            'Nombre' => 'Persona',
            'Apellido' => '02',
            'username' => 'persona02',
            'cod_fiscalia' => 502, // Fiscalia de Valparaiso
            'id_escalafon' => 1,
            'flag_lider' => true,
            'flag_activo' => true,
            'password' => bcrypt('1234'),
            'id_rol' => 1,
        ]);

        // Crear algunos líderes
        TblPersona::create([
            'Nombre' => 'J',
            'Apellido' => 'D',
            'username' => 'jd502',
            'cod_fiscalia' => 502, // Fiscalia de Valparaiso
            'id_escalafon' => 1,
            'flag_lider' => true,
            'flag_activo' => true,
            'password' => bcrypt('1234'),
            'id_rol' => 2,
        ]);

        TblPersona::create([
            'Nombre' => 'Persona',
            'Apellido' => '03',
            'username' => 'persona03',
            'cod_fiscalia' => 502, // Fiscalia de Viña del Mar
            'id_escalafon' => 1,
            'flag_lider' => false,
            'flag_activo' => true,
            'password' => bcrypt('1234'),
            'id_rol' => 1,
        ]);

        TblPersona::create([
            'Nombre' => 'Andrea',
            'Apellido' => 'Silva',
            'username' => 'udp',
            'cod_fiscalia' => 504, // Fiscalia de Quilpue
            'id_escalafon' => 1,
            'flag_lider' => true,
            'flag_activo' => true,
            'password' => bcrypt('1234'),
            'id_rol' => 3,
        ]);

        // Crear algunas personas regulares
        TblPersona::create([
            'Nombre' => 'DER',
            'Apellido' => '',
            'username' => 'DER',
            'cod_fiscalia' => 1,
            'id_escalafon' => 1,
            'flag_lider' => false,
            'flag_activo' => true,
            'password' => bcrypt('1234'),
            'id_rol' => 5,
        ]);

        TblPersona::create([
            'Nombre' => 'JUDP',
            'Apellido' => '',
            'username' => 'JUDP',
            'cod_fiscalia' => 2,
            'id_escalafon' => 1,
            'flag_lider' => false,
            'flag_activo' => true,
            'password' => bcrypt('1234'),
            'id_rol' => 4,
        ]);

        TblPersona::create([
            'Nombre' => 'JD',
            'Apellido' => 'Vergas',
            'username' => 'jd',
            'cod_fiscalia' => 501,
            'id_escalafon' => 1,
            'flag_lider' => false,
            'flag_activo' => true,
            'password' => bcrypt('1234'),
            'id_rol' => 2,
        ]);

        // Crear usuarios de prueba para desarrollo
        TblPersona::create([
            'Nombre' => 'Persona',
            'Apellido' => '01',
            'username' => 'persona01',
            'cod_fiscalia' => 501,
            'id_escalafon' => 1,
            'flag_lider' => false,
            'flag_activo' => true,
            'password' => bcrypt('1234'),
            'id_rol' => 1,
        ]);

        // Crear algunos usuarios adicionales para pruebas masivas
        for ($i = 4; $i <= 8; $i++) {
            TblPersona::create([
                'Nombre' => "Usuario Test {$i}",
                'Apellido' => "Apellido {$i}",
                'username' => "persona0{$i}",
                'cod_fiscalia' => 501,
                'id_escalafon' => 1,
                'flag_lider' => false,
                'flag_activo' => true,
                'password' => bcrypt('1234'),
                'id_rol' => 1,
            ]);
        }
    }
}
