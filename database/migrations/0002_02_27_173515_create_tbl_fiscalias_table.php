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

        Schema::create('tbl_fiscalias', function (Blueprint $table) {
            $table->bigInteger('cod_fiscalia')->primary(); // Clave primaria personalizada
            $table->string('gls_fiscalia');
            $table->timestamps();
        });



        // Schema::create('tbl_fiscalias', function (Blueprint $table) {
        //     $table->id()->foreign('tbl_persona.cod_fiscalia');
        //     $table->string('gls_fiscalia');
        //     $table->bigInteger('cod_fiscalia')->default(0);
        //     $table->timestamps();
        // });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_fiscalias');
    }
};
