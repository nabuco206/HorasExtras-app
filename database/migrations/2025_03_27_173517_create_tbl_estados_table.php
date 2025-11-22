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


        Schema::create('tbl_estados', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique(); // Código lógico: APROBADO_JEFE, RECHAZADO_JEFE, etc.
            $table->string('descripcion', 150);
            $table->string('tipo_accion', 50)->nullable(); // 'SUMA', 'RESTA', 'NINGUNA'
            $table->string('flujo', 50)->nullable(); // 'TIEMPO', 'DINERO', 'AMBOS'
            $table->foreignId('flujo_id')->nullable()->constrained('tbl_flujos');
            $table->boolean('es_final')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_estados');
    }
};
