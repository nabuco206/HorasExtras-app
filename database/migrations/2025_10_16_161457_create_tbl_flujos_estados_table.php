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
        Schema::create('tbl_flujos_estados', function (Blueprint $table) {
           $table->id();
            $table->foreignId('flujo_id')->constrained('tbl_flujos')->onDelete('cascade');
            $table->foreignId('estado_origen_id')->constrained('tbl_estados')->onDelete('cascade');
            $table->foreignId('estado_destino_id')->constrained('tbl_estados')->onDelete('cascade');
            $table->integer('rol_autorizado')->nullable(); // Ej: 'JEFE', 'UPER', 'DIRECCION'
            $table->text('condicion_sql')->nullable(); // Reglas adicionales (ej: total_min > 60)
            $table->integer('orden')->default(1);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['flujo_id', 'estado_origen_id', 'estado_destino_id'], 'flujo_estado_unico');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_flujos_estados');
    }
};
