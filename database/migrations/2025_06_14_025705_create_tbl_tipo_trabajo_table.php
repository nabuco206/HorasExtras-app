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


         Schema::create('tbl_tipo_trabajo', function (Blueprint $table) {
            $table->id();
            $table->string('gls_tipo_trabajo');
            $table->boolean('flag_activo')->default(true)->comment('Indica si el tipo de trabajo está activo en el sistema');
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::dropIfExists('tbl_tipo_trabajo');

    }
};
