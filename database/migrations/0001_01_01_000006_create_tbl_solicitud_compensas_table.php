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
            $table->bigInteger('cod_fiscalia');
            $table->foreign('cod_fiscalia')->references('cod_fiscalia')->on('tbl_fiscalias');
            // $table->foreignId('id_tipo_trabajo')->nullable()->constrained('tbl_tipo_trabajo');
            // $table->foreignId('tipo_trabajo_id')->nullable()->constrained('tipo_trabajos');

            $table->date('fecha');
            $table->time('hrs_inicial');
            $table->time('hrs_final');

            $table->bigInteger('id_estado')->default(0);
            // $table->foreign('id_estado')->references('id')->on('tbl_estados');

            // $table->char('tipo_solicitud');
            // $table->bigInteger('id_tipo_compensacion');
            // $table->foreign('id_tipo_compensacion')->references('id')->on('tbl_tipo_compensacions')->onDelete('cascade');
//             $table->foreign('id_tipoCompensacion')->references('id')->on('tbl_tipo_compensacions');
            // $table->bigInteger('min_reales')->nullable();
            // $table->bigInteger('min_25')->nullable();
            // $table->bigInteger('min_50')->nullable();
            $table->bigInteger('total_min')->nullable();
            $table->timestamps();
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