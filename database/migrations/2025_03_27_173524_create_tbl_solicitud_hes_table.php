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
        Schema::disableForeignKeyConstraints();

        Schema::create('tbl_solicitud_hes', function (Blueprint $table) {
            $table->id()->foreign('tbl_seguimiento_solicitud.id_solicitud_he');
            $table->string('username');
            $table->bigInteger('tipo_trabajo');
            $table->date('fecha');
            $table->time('hrs_inicial');
            $table->time('hrs_final');
            $table->bigInteger('id_estado');
            $table->foreign('id_estado')->references('id')->on('tbl_estado');
            $table->char('tipo_solicitud');
            $table->date('fecha_evento');
            $table->time('hrs_inicio');
            $table->time('hrs_fin');
            $table->bigInteger('id_tipoCompensacion');
            $table->bigInteger('min_25');
            $table->bigInteger('min_50');
            $table->bigInteger('total_min');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_solicitud_hes');
    }
};
