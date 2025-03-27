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

        Schema::create('tbl_seguimiento_solicituds', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_solicitud_he');
            $table->string('username');
            $table->bigInteger('id_estado');
            $table->foreign('id_estado')->references('id')->on('tbl_estado');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_seguimiento_solicituds');
    }
};
