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


        Schema::create('tbl_solicitud_compensas', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->foreign('username')->references('username')->on('tbl_personas')->onDelete('cascade');
            $table->bigInteger('cod_fiscalia');
            $table->foreign('cod_fiscalia')->references('cod_fiscalia')->on('tbl_fiscalias');

            $table->date('fecha_solicitud');
            $table->time('hrs_inicial');
            $table->time('hrs_final');
            $table->integer('minutos_solicitados');
            $table->integer('minutos_aprobados')->nullable();

            $table->bigInteger('id_estado')->default(1); // 1=Pendiente, 2=Aprobado, 3=Rechazado
            $table->foreign('id_estado')->references('id')->on('tbl_estados');

            $table->text('observaciones')->nullable();
            $table->string('aprobado_por')->nullable();
            $table->timestamp('fecha_aprobacion')->nullable();

            $table->timestamps();

            // Índices para consultas optimizadas
            $table->index(['username', 'fecha_solicitud']);
            $table->index(['id_estado']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_solicitud_compensas');
    }
};
