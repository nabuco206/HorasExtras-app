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
use App\Models\TblTipoEstado;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

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
         TblEscalafon::create([
            'gls_escalafon' => 'ADMINISTRATIVO',
        ]);

        $fiscalias = [
            ['cod_fiscalia' => 01, 'gls_fiscalia' => 'UGI - FR'],
            ['cod_fiscalia' => 02, 'gls_fiscalia' => 'UDP - FR'],
            ['cod_fiscalia' => 03, 'gls_fiscalia' => 'UAF - FR'],
            ['cod_fiscalia' => 04, 'gls_fiscalia' => 'URAVYT - FR'],
            ['cod_fiscalia' => 501, 'gls_fiscalia' => 'Fiscalia de Valparaiso'],
            ['cod_fiscalia' => 502, 'gls_fiscalia' => 'Fiscalia de Viña del Mar'],
            ['cod_fiscalia' => 504, 'gls_fiscalia' => 'Fiscalia de Quilpue'],
            ['cod_fiscalia' => 507, 'gls_fiscalia' => 'Fiscalia de Villa Alemana'],
            ['cod_fiscalia' => 511, 'gls_fiscalia' => 'Fiscalia de Limache'],
            ['cod_fiscalia' => 508, 'gls_fiscalia' => 'Fiscalia de Quillota'],
            ['cod_fiscalia' => 509, 'gls_fiscalia' => 'Fiscalia de La Calera'],
            ['cod_fiscalia' => 503, 'gls_fiscalia' => 'Fiscalia de San Antonio'],
            ['cod_fiscalia' => 515, 'gls_fiscalia' => 'Fiscalia de Casablanca'],
        ];

        DB::table('tbl_fiscalias')->truncate();
        foreach ($fiscalias as $fiscalia) {
            TblFiscalia::create($fiscalia);
        }

        TblPersona::truncate();
        $persona1 = TblPersona::create([
            'nombre' => 'CristianCRM',
            'apellido' => 'Rojas',
            'username' => 'crojasm',
            'cod_fiscalia' => 501,
            'id_escalafon' => 1,
            'flag_lider' => true,
            'password' => bcrypt('1234'),
            'id_rol' => 1,
        ]);

        $persona2 = TblPersona::create([
            'nombre' => 'Persona',
            'apellido' => '01',
            'username' => 'persona01',
            'cod_fiscalia' => 501,
            'id_escalafon' => 1,
            'flag_lider' => false,
            'password' => bcrypt('1234'),
            'id_rol' => 1,
        ]);


        TblTipoEstado::truncate();
        TblTipoEstado::create([
            'id' => 0,
            'gls_tipo_estado' => 'Flujo HE',
        ]);
        TblTipoEstado::create([
            'id' => 1,
            'gls_tipo_estado' => 'Flujo Compensacion',
        ]);
        TblTipoEstado::create([
            'id' => 2,
            'gls_tipo_estado' => 'Bolson',
        ]);


        TblTipoCompensacion::truncate();
        TblTipoCompensacion::create([
            'id' => 0,
            'gls_tipo_compensacion' => 'Compensación en Hrs',
        ]);
        TblTipoCompensacion::create([
            'id' => 1,
            'gls_tipo_compensacion' => 'Pago',
        ]);

        // TblEstado::truncate();
        // TblEstado::create([
        //     'id' => 0,
        //     'gls_estado' => 'Ingreso',
        // ]);

        // TblEstado::create([
        //     'id' => 1,
        //     'gls_estado' => 'Aprobado',
        // ]);
        // TblEstado::create([
        //     'id' => 2,
        //     'gls_estado' => 'Cerrado',
        // ]);
        // TblEstado::create([
        //     'id' => 3,
        //     'gls_estado' => 'Rechazado',
        // ]);
        // TblEstado::create([
        //     'id' => 4,
        //     'gls_estado' => 'Boton Apr',
        // ]);
        // TblTipoTrabajo::truncate();
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

        // Crear las solicitudes de prueba específicas
        $this->call(TblSolicitudHeSeeder::class);

        // Crear personas con flag_lider
        $this->call(TblPersonaSeeder::class);

        $this->call(TblEstadoSeeder::class);


    }
}
