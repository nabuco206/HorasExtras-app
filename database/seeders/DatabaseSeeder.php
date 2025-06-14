<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\TblFiscalia;
use App\Models\TblEscalafon;
use App\Models\TblPersona;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // TblFiscalia::factory(10)->create([
        //     'id'=>501,
        //     'gls_fiscalia' => 'Fiscalía de Valparaíso',
        // ]);
        TblEscalafon::create([
            'gls_escalafon' => 'TESNICO',
        ]);
        
        $fiscalias = [
            'Fiscalia de Valparaiso',
            'Fiscalia de Viña del Mar',
            'Fiscalia de Quilpue',
            'Fiscalia de Villa Alemana',
            'Fiscalia de Limache',
            'Fiscalia de Quillota',
            'Fiscalia de La Calera',
            'Fiscalia de San Antonio',
            'Fiscalia de Casablanca',
        ];
       
        DB::table('tbl_fiscalias')->truncate();
        foreach ($fiscalias as $fiscalia) {
            TblFiscalia::create([
                'gls_fiscalia' => $fiscalia,
            ]);
        }
        

         TblPersona::create([
            'Nombre' => 'CristianCRM',
            'Apellido' => 'Rojas',
            'UserName' => 'crojasm',
            'cod_fiscalia' => 1,
            'id_escalafon' => 1,
        ]);

        // DB::table('users')->truncate();
        User::create([
            'name' => 'crojasm',
            'email' => 'crojasm@minpublico.cl',
            'password' => bcrypt('1234'),
            'persona_id' => 1, // Asegúrate de tener este campo en la migración
            'rol' => 1, 
        ]);

        
    }
}
