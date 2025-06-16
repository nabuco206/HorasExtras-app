<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\TblFiscalia;
use App\Models\TblEscalafon;
use App\Models\TblPersona;
use App\Models\TblFeriado;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Deshabilita claves foráneas (solo para SQLite)
        DB::statement('PRAGMA foreign_keys = OFF;');

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
            'persona_id' => 1, 
            'id_rol' => 0, 
        ]);
         User::create([
            'name' => 'persona01',
            'email' => 'persona01@minpublico.cl',
            'password' => bcrypt('1234'),
            'persona_id' => 1, 
            'id_rol' => 0, 
        ]);

        DB::table('tbl_tipo_compensacions')->insert([
            [
                'id' => 0,
                'gls_tipoCompensacion' => 'Compensación en Hrs',
            ],
            [
                'id' => 1,
                'gls_tipoCompensacion' => 'Pago',
            ]
        ]);
       

        DB::table('tbl_estados')->insert([
            [
            'id' => 0,
            'gls_estado' => 'Ingreso',
            ],
            [
            'id' => 1,
            'gls_estado' => 'Aprobado',
            ],
        ]);

        DB::table('tbl_tipo_trabajo')->insert([
            [
            'id' => 0,
            'gls_tipo_trabajo' => 'EIVG',
            ],
            [
            'id' => 1,
            'gls_tipo_trabajo' => 'Causas Rezagadas',
            ],   
        ]);
         


        $feriados = [
            ['fecha' => '01-01', 'descripcion' => 'Año Nuevo'],
            ['fecha' => '04-18', 'descripcion' => 'Viernes Santo'],
            ['fecha' => '04-19', 'descripcion' => 'Sábado Santo'],
            ['fecha' => '05-01', 'descripcion' => 'Día Nacional del Trabajo'],
            ['fecha' => '05-21', 'descripcion' => 'Día de las Glorias Navales'],
            ['fecha' => '06-20', 'descripcion' => 'Día Nacional de los Pueblos Indígenas'],
            ['fecha' => '06-29', 'descripcion' => 'San Pedro y San Pablo'],
            ['fecha' => '07-16', 'descripcion' => 'Día de la Virgen del Carmen'],
            ['fecha' => '08-15', 'descripcion' => 'Asunción de la Virgen'],
            ['fecha' => '09-18', 'descripcion' => 'Independencia Nacional'],
            ['fecha' => '09-19', 'descripcion' => 'Día de las Glorias del Ejército'],
            ['fecha' => '10-12', 'descripcion' => 'Encuentro de Dos Mundos'],
            ['fecha' => '10-31', 'descripcion' => 'Día de las Iglesias Evangélicas y'],
            ['fecha' => '11-01', 'descripcion' => 'Día de Todos los Santos'],
            ['fecha' => '12-08', 'descripcion' => 'Inmaculada Concepción'],
            ['fecha' => '12-25', 'descripcion' => 'Navidad'],
        ];

        foreach ($feriados as $feriado) {
            TblFeriado::create($feriado);
        }

        
        // $this->call(TblSolicitudHeSeeder::class);
        
    }
}
