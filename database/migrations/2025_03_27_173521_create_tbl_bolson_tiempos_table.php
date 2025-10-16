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


        Schema::create('tbl_bolson_tiempos', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->foreign('username')->references('username')->on('tbl_personas')->onDelete('cascade');
            $table->bigInteger('id_solicitud_he');
            $table->foreign('id_solicitud_he')->references('id')->on('tbl_solicitud_hes')->onDelete('cascade');
            $table->date('fecha_crea');
            $table->integer('minutos');
            $table->date('fecha_vence');
            $table->integer('saldo_min');
            $table->string('origen')->default('HE_APROBADA')->comment('Origen del bolsón: HE_APROBADA, AJUSTE, etc');
            $table->boolean('activo')->default(true);
            $table->timestamps();

            // Índices para consultas optimizadas
            $table->index(['username', 'fecha_vence']);
            $table->index(['fecha_vence', 'activo']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_bolson_tiempos');
    }
};
