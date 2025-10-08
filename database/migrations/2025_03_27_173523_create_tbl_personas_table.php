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


        Schema::create('tbl_personas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('username')->unique();
            $table->bigInteger('cod_fiscalia')->nullable();
            $table->foreign('cod_fiscalia')->references('cod_fiscalia')->on('tbl_fiscalias');
            $table->foreignId('id_escalafon')->nullable()->constrained('tbl_escalafons');
            $table->foreignId('id_turno')->nullable()->constrained('tbl_turnos');
            $table->boolean('flag_lider')->default(true)->comment('Indica si la persona es lider en el sistema');
            $table->boolean('flag_activo')->default(0)->comment('Indica estado del registro');
            $table->foreignId('id_rol')->nullable()->constrained('tbl_rol');
            $table->string('password');
            $table->string('remember_token')->nullable();
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    Schema::disableForeignKeyConstraints();
    Schema::dropIfExists('tbl_personas');
    Schema::enableForeignKeyConstraints();
    }
};
