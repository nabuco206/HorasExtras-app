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
        Schema::table('tbl_feriados', function (Blueprint $table) {
            $table->boolean('flag_activo')->default(true)->comment('Indica si el feriado está activo en el sistema');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_feriados', function (Blueprint $table) {
            $table->dropColumn('flag_activo');
        });
    }
};
