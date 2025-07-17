<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, crear una columna temporal para las fechas convertidas
        Schema::table('tbl_feriados', function (Blueprint $table) {
            $table->date('fecha_temp')->nullable();
        });
        
        // Convertir los datos existentes de MM-DD a formato de fecha completo (usando 2024 como año base)
        $registros = DB::table('tbl_feriados')->get();
        foreach ($registros as $registro) {
            $fechaPartes = explode('-', $registro->fecha);
            if (count($fechaPartes) == 2) {
                $mes = $fechaPartes[0];
                $dia = $fechaPartes[1];
                $fechaCompleta = "2024-{$mes}-{$dia}";
                
                DB::table('tbl_feriados')
                    ->where('id', $registro->id)
                    ->update(['fecha_temp' => $fechaCompleta]);
            }
        }
        
        // Eliminar la columna original y renombrar la temporal
        Schema::table('tbl_feriados', function (Blueprint $table) {
            $table->dropUnique(['fecha']);
            $table->dropColumn('fecha');
        });
        
        Schema::table('tbl_feriados', function (Blueprint $table) {
            $table->renameColumn('fecha_temp', 'fecha');
        });
        
        // Agregar restricción unique a la nueva columna fecha
        Schema::table('tbl_feriados', function (Blueprint $table) {
            $table->unique('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Crear columna temporal para revertir
        Schema::table('tbl_feriados', function (Blueprint $table) {
            $table->string('fecha_temp', 5)->nullable();
        });
        
        // Convertir fechas de vuelta al formato MM-DD
        $registros = DB::table('tbl_feriados')->get();
        foreach ($registros as $registro) {
            $fecha = date('m-d', strtotime($registro->fecha));
            DB::table('tbl_feriados')
                ->where('id', $registro->id)
                ->update(['fecha_temp' => $fecha]);
        }
        
        // Eliminar la columna de fecha actual y renombrar la temporal
        Schema::table('tbl_feriados', function (Blueprint $table) {
            $table->dropUnique(['fecha']);
            $table->dropColumn('fecha');
        });
        
        Schema::table('tbl_feriados', function (Blueprint $table) {
            $table->renameColumn('fecha_temp', 'fecha');
            $table->unique('fecha');
        });
    }
};
