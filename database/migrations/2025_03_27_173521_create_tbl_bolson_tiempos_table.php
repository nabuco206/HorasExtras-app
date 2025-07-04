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

        Schema::create('tbl_bolson_tiempos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_solicitud');
            $table->foreign('id_solicitud')->references('id')->on('tbl_solicitud_he');
            $table->bigInteger('tiempo');
            $table->char('estado');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_bolson_tiempos');
    }
};
