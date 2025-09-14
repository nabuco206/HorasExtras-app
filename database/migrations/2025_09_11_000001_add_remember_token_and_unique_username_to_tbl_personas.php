<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tbl_personas', function (Blueprint $table) {
            // Agregar campo remember_token si no existe
            if (!Schema::hasColumn('tbl_personas', 'remember_token')) {
                $table->string('remember_token', 100)->nullable()->after('password');
            }
            // Hacer UserName único y no nulo
            $table->string('UserName')->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tbl_personas', function (Blueprint $table) {
            if (Schema::hasColumn('tbl_personas', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
            // No se puede revertir el unique fácilmente sin saber el estado anterior
        });
    }
};
