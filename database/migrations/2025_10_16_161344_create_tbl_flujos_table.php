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
        Schema::create('tbl_flujos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 50)->unique(); // 'FLUJO_TIEMPO', 'FLUJO_DINERO', etc.
            $table->string('descripcion', 150);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_flujos');
    }
};
