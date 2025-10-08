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
        Schema::create('tbl_bolson_hists', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_bolson');
            $table->string('username');
            $table->string('accion');
            $table->bigInteger('minutos_afectados');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_bolson_hists');
    }
};
