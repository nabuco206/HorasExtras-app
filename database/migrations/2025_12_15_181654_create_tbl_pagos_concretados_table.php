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
        Schema::create('tbl_pagos_concretados', function (Blueprint $table) {
            $table->id();
            $table->string('sociedad_id');
            $table->date('fecha_pago');
            $table->string('id_empleado');
            $table->string('rut');
            $table->string('nombre');
            $table->decimal('sobretiempo_normal_25', 10, 2);
            $table->string('moneda_id');
            $table->decimal('sobretiempo_especial_50', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_pagos_concretados');
    }
};
