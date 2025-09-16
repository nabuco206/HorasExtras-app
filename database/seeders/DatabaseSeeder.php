<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;

use App\Models\TblFiscalia;
use App\Models\TblEscalafon;
use App\Models\TblPersona;
use App\Models\TblFeriado;
use App\Models\TblTipoCompensacion;
use App\Models\TblEstado;
use App\Models\TblTipoTrabajo;
use App\Models\Tbl;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Comentado para PostgreSQL
        // DB::statement('PRAGMA foreign_keys = OFF;');

        // User::factory(10)->create();
        // TblFiscalia::factory(10)->create([
        //     'id'=>501,
        //     'gls_fiscalia' => 'Fiscalía de Valparaíso',
        // ]);



            DB::table('tbl_rol')->insert([
                           ['gls_rol' => 'Usuario']
                    ]);

            DB::table('tbl_rol')->insert([
                           ['gls_rol' => 'Lider']
                    ]);


        TblEscalafon::truncate();


        TblEscalafon::create([
            'gls_escalafon' => 'TECNICO',
        ]);
        TblEscalafon::create([
            'gls_escalafon' => 'PROFESIONAL',
        ]);
        TblEscalafon::create([
            'gls_escalafon' => 'AUXILIAR',
        ]);

        $fiscalias = [
            ['cod_fiscalia' => 501, 'gls_fiscalia' => 'Fiscalia de Valparaiso'],
            ['cod_fiscalia' => 502, 'gls_fiscalia' => 'Fiscalia de Viña del Mar'],
            ['cod_fiscalia' => 504, 'gls_fiscalia' => 'Fiscalia de Quilpue'],
            ['cod_fiscalia' => 507, 'gls_fiscalia' => 'Fiscalia de Villa Alemana'],
            ['cod_fiscalia' => 5, 'gls_fiscalia' => 'Fiscalia de Limache'],
            ['cod_fiscalia' => 6, 'gls_fiscalia' => 'Fiscalia de Quillota'],
            ['cod_fiscalia' => 7, 'gls_fiscalia' => 'Fiscalia de La Calera'],
            ['cod_fiscalia' => 503, 'gls_fiscalia' => 'Fiscalia de San Antonio'],
            ['cod_fiscalia' => 515, 'gls_fiscalia' => 'Fiscalia de Casablanca'],
        ];

        DB::table('tbl_fiscalias')->truncate();
        foreach ($fiscalias as $fiscalia) {
            TblFiscalia::create($fiscalia);
        }

        TblPersona::truncate();
        $persona1 = TblPersona::create([
            'Nombre' => 'CristianCRM',
            'Apellido' => 'Rojas',
            'UserName' => 'crojasm',
            'cod_fiscalia' => 501,
            'id_escalafon' => 1,
            'flag_lider' => true,
            'password' => bcrypt('1234'),
            'id_rol' => 1,
        ]);

        $persona2 = TblPersona::create([
            'Nombre' => 'Persona',
            'Apellido' => '01',
            'UserName' => 'persona01',
            'cod_fiscalia' => 501,
            'id_escalafon' => 1,
            'flag_lider' => false,
            'password' => bcrypt('1234'),
            'id_rol' => 1,
        ]);



        TblTipoCompensacion::truncate();
        TblTipoCompensacion::create([
            'id' => 0,
            'gls_tipoCompensacion' => 'Compensación en Hrs',
        ]);

        TblTipoCompensacion::create([
            'id' => 1,
            'gls_tipoCompensacion' => 'Pago',
        ]);

        TblEstado::truncate();
        TblEstado::create([
            'id' => 0,
            'gls_estado' => 'Ingreso',
        ]);

        TblEstado::create([
            'id' => 1,
            'gls_estado' => 'Aprobado',
        ]);
        TblTipoTrabajo::truncate();
        TblTipoTrabajo::create([
            'id' => 0,
            'gls_tipo_trabajo' => 'EIVG',
        ]);

        TblTipoTrabajo::create([
            'id' => 1,
            'gls_tipo_trabajo' => 'Causas Rezagadas',
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
        TblFeriado::truncate();
        foreach ($feriados as $feriado) {
            TblFeriado::create($feriado);
        }


        // $this->call(TblSolicitudHeSeeder::class);

        // Crear personas con flag_lider
        $this->call(TblPersonaSeeder::class);

    }
}
