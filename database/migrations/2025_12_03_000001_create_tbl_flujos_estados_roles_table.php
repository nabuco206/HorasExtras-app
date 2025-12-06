<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_flujos_estados_roles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('flujo_estado_id');
            $table->unsignedInteger('rol_id')->nullable();
            $table->timestamps();

            // índice para búsquedas frecuentes
            $table->index(['flujo_estado_id', 'rol_id'], 'idx_flujo_estado_rol');

            // FK opcional (no obligatorio para compatibilidad con sqlite seeds locales)
            // Si se desea, el usuario puede añadir FK a tbl_flujos_estados y roles
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_flujos_estados_roles');
    }
};
