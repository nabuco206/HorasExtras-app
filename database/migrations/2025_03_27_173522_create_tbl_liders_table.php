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


        Schema::create('tbl_liders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->nullable()->constrained('tbl_personas');
            $table->bigInteger('cod_fiscalia');
            $table->string('gls_unidad');
            $table->boolean('flag_activo')->default(true)->comment('Indica si el líder está activo en el sistema');
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_liders');
    }
};
