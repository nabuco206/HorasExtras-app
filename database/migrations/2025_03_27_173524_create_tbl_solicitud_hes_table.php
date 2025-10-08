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


        Schema::create('tbl_solicitud_hes', function (Blueprint $table) {
            $table->id()->foreign('tbl_seguimiento_solicitud.id_solicitud_he');
            $table->string('username');
            $table->bigInteger('cod_fiscalia');
            $table->foreign('cod_fiscalia')->references('cod_fiscalia')->on('tbl_fiscalias');
            $table->bigInteger('id_tipo_trabajo');
            $table->foreign('id_tipo_trabajo')->references('id')->on('tbl_tipo_trabajo')->onDelete('cascade');
            $table->date('fecha');
            $table->time('hrs_inicial');
            $table->time('hrs_final');

            $table->bigInteger('id_estado')->default(0);
            $table->foreign('id_estado')->references('id')->on('tbl_estados');
            $table->bigInteger('id_tipo_compensacion');
            $table->bigInteger('min_reales')->nullable();
            $table->bigInteger('min_25')->nullable();
            $table->bigInteger('min_50')->nullable();
            $table->bigInteger('total_min')->nullable();
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_solicitud_hes');
    }
};
