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


        Schema::create('tbl_seguimiento_solicituds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_solicitud_he');
            $table->string('username');
            // $table->bigInteger('id_estado');
            $table->foreignId('id_estado')->nullable()->constrained('tbl_estados');
            $table->foreign('username')->references('username')->on('tbl_personas')->onDelete('cascade');
            $table->timestamps();



        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_seguimiento_solicituds');
    }
};
