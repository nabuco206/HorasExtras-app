<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tbl_config_horas_extras', function (Blueprint $table) {
            $table->string('clave')->nullable()->after('id');
            $table->string('descripcion')->nullable()->after('clave');
            $table->time('hora_inicio')->nullable()->after('descripcion');
            $table->time('hora_fin')->nullable()->after('hora_inicio');
            $table->decimal('porcentaje', 5, 2)->nullable()->after('hora_fin');
            $table->json('dias_semana')->nullable()->after('porcentaje');
            $table->boolean('aplica_feriados')->default(false)->after('dias_semana');
            $table->boolean('aplica_fines_semana')->default(false)->after('aplica_feriados');
            $table->boolean('activo')->default(true)->after('aplica_fines_semana');
            $table->integer('orden')->default(0)->after('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_config_horas_extras', function (Blueprint $table) {
            $table->dropColumn([
                'clave',
                'descripcion',
                'hora_inicio',
                'hora_fin',
                'porcentaje',
                'dias_semana',
                'aplica_feriados',
                'aplica_fines_semana',
                'activo',
                'orden'
            ]);
        });
    }
};
