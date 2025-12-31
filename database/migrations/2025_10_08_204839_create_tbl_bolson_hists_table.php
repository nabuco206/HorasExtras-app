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
        Schema::create('tbl_bolson_hists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_bolson_tiempo');
            $table->foreign('id_bolson_tiempo')
                ->references('id')
                ->on('tbl_bolson_tiempos');
            
            $table->string('username');
            $table->foreign('username')->references('username')->on('tbl_personas')->onDelete('cascade');
            
            $table->integer('id_solicitud_compensa')->nullable();

            // $table->unsignedBigInteger('id_solicitud_compensa');
            // $table->foreign('id_solicitud_compensa')
            //     ->references('id')
            //     ->on('tbl_solicitud_compensas');

            $table->string('accion'); // CREACION, USO, VENCIMIENTO, AJUSTE
            $table->integer('minutos_afectados');
            $table->integer('saldo_anterior');
            $table->integer('saldo_nuevo');
            $table->text('observaciones')->nullable();

            $table->timestamps();

            // Índices para consultas optimizadas
            $table->index(['id_bolson_tiempo', 'accion']);
            $table->index(['username', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_bolson_hists');
    }
};
