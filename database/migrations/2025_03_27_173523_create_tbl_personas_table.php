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
            $table->string('Nombre');
            $table->string('Apellido');
            $table->string('UserName');
            $table->bigInteger('cod_fiscalia')->nullable();
            $table->foreign('cod_fiscalia')->references('cod_fiscalia')->on('tbl_fiscalias');
            $table->foreignId('id_escalafon')->nullable()->constrained('tbl_escalafons');
            $table->unsignedBigInteger('id_turno')->default(0)->after('id_escalafon');
            $table->boolean('flag_lider')->default(true)->comment('Indica si la persona es lider en el sistema');
            $table->boolean('flag_activo')->default(true)->comment('Indica si la persona está activa en el sistema');
            $table->unsignedBigInteger('id_rol')->default(0);
            $table->string('password');
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_personas');
    }
};
