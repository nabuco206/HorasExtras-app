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
        Schema::create('tbl_turnos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('dias')->default('1,2,3,4,5'); // 1=Lunes, 7=Domingo, separados por coma
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_turnos');
    }
};
