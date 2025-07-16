<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First check if the column exists and add flag_activo as a new column
        Schema::table('tbl_liders', function (Blueprint $table) {
            $table->boolean('flag_activo')->default(true)->comment('Indica si el líder está activo en el sistema');
        });
        
        // Copy data from activo to flag_activo (assuming 'Y' = true, 'N' = false)
        DB::statement("UPDATE tbl_liders SET flag_activo = CASE WHEN activo = 'Y' THEN true ELSE false END");
        
        // Drop the old activo column
        Schema::table('tbl_liders', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the activo column
        Schema::table('tbl_liders', function (Blueprint $table) {
            $table->char('activo', 1)->default('Y');
        });
        
        // Copy data back from flag_activo to activo
        DB::statement("UPDATE tbl_liders SET activo = CASE WHEN flag_activo = true THEN 'Y' ELSE 'N' END");
        
        // Drop the flag_activo column
        Schema::table('tbl_liders', function (Blueprint $table) {
            $table->dropColumn('flag_activo');
        });
    }
};
