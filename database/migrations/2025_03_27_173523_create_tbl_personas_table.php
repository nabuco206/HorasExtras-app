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

        Schema::create('tbl_personas', function (Blueprint $table) {
            $table->id();
            $table->string('Nombre');
            $table->string('Apellido');
            $table->string('UserName');
            $table->bigInteger('cod_fiscalia');
            $table->foreign('cod_fiscalia')->references('id')->on('tbl_fiscalias');
            $table->bigInteger('id_escalafon');
            $table->foreign('id_escalafon')->references('id')->on('tbl_escalafons');
             $table->unsignedBigInteger('id_turno')->default(0)->after('id_escalafon');
            // Si quieres relación foránea, descomenta la siguiente línea:
            // $table->foreign('id_turno')->references('id')->on('tbl_turnos');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_personas');
    }
};
