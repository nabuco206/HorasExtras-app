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
            // $table->foreign('username')->references('name')->on('users');
            $table->bigInteger('id_tipo_trabajo');
            $table->foreign('id_tipo_trabajo')->references('id')->on('tbl_tipo_trabajo');
            $table->date('fecha');
            $table->time('hrs_inicial');
            $table->time('hrs_final');
            $table->bigInteger('id_estado')->default(0);
            $table->foreign('id_estado')->references('id')->on('tbl_estados');
            $table->char('tipo_solicitud');
            $table->date('fecha_evento')->nullable();
            $table->time('hrs_inicio')->nullable();
            $table->time('hrs_fin')->nullable();
            $table->bigInteger('id_tipoCompensacion');
            $table->foreign('id_tipoCompensacion')->references('id')->on('tbl_tipo_compensacions');
            $table->bigInteger('min_reales')->nullable();
            $table->bigInteger('min_25')->nullable();
            $table->bigInteger('min_50')->nullable();
            $table->bigInteger('total_min')->nullable();
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
