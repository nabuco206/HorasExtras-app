<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tbl_lider', function (Blueprint $table) {
            $table->id();
            $table->string('username');
//             // $table->foreign('username')->references('name')->on('users'); // Temporalmente comentado
            $table->bigInteger('id_fiscalia');
//             // $table->foreign('id_fiscalia')->references('id')->on('tbl_fiscalia'); // Temporalmente comentado
            $table->char('activo');
        
            $table->timestamps();
        }); 
    }

    public function down(): void
    {
        Schema::dropIfExists('tbl_lider');
    }
};
