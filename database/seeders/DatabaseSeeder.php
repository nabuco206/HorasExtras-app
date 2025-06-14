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
            ['id' => 501, 'gls_fiscalia' => 'Fiscalia de Valparaiso'],
            ['id' => 502, 'gls_fiscalia' => 'Fiscalia de Viña del Mar'],
            ['id' => 504, 'gls_fiscalia' => 'Fiscalia de Quilpue'],
            ['id' => 507, 'gls_fiscalia' => 'Fiscalia de Villa Alemana'],
            ['id' => 5, 'gls_fiscalia' => 'Fiscalia de Limache'],
            ['id' => 6, 'gls_fiscalia' => 'Fiscalia de Quillota'],
            ['id' => 7, 'gls_fiscalia' => 'Fiscalia de La Calera'],
            ['id' => 503, 'gls_fiscalia' => 'Fiscalia de San Antonio'],
            ['id' => 515, 'gls_fiscalia' => 'Fiscalia de Casablanca'],
        ];
       
        DB::table('tbl_fiscalias')->truncate();
        foreach ($fiscalias as $fiscalia) {
            TblFiscalia::create($fiscalia);
        }
        

         TblPersona::create([
            'Nombre' => 'CristianCRM',
            'Apellido' => 'Rojas',
            'UserName' => 'crojasm',
            'cod_fiscalia' => 501,
            'id_escalafon' => 1,
        ]);

        TblPersona::create([
            'Nombre' => 'Persona',
            'Apellido' => '01',
            'UserName' => 'persona01',
            'cod_fiscalia' => 501,
            'id_escalafon' => 1,
        ]);

        DB::table('users')->truncate();
        User::create([
            'name' => 'crojasm',
            'email' => 'crojasm@minpublico.cl',
            'password' => bcrypt('1234'),
            'persona_id' => 1, // Asegúrate de tener este campo en la migración
            'rol' => 1, 
        ]);
         User::create([
            'name' => 'persona01',
            'email' => 'persona01@minpublico.cl',
            'password' => bcrypt('1234'),
            'persona_id' => 1, // Asegúrate de tener este campo en la migración
            'rol' => 1, 
        ]);

        DB::table('tbl_tipo_compensacions')->insert([
            'id' => 0,
            'gls_tipoCompensacion' => 'HE',
            // agrega otros campos si existen
        ]);

        DB::table('tbl_estados')->insert([
            'id' => 0,
            'gls_estado' => 'Ingreso',
            // agrega otros campos si existen
        ]);

        DB::table('tbl_tipo_trabajo')->insert([
            'id' => 0,
            'gls_tipo_trabajo' => 'EIVG',
            // agrega otros campos si existen
        ]);
         DB::table('tbl_tipo_trabajo')->insert([
            'id' => 1,
            'gls_tipo_trabajo' => 'REZAGADOS',
            // agrega otros campos si existen
        ]);

        

        
    }
}
