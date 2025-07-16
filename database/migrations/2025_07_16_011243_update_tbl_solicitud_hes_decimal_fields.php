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
        // Primero vamos a eliminar las columnas y recrearlas como decimal
        Schema::table('tbl_solicitud_hes', function (Blueprint $table) {
            $table->dropColumn(['min_reales', 'min_25', 'min_50', 'total_min']);
        });
        
        Schema::table('tbl_solicitud_hes', function (Blueprint $table) {
            $table->decimal('min_reales', 8, 2)->nullable()->after('id_tipoCompensacion');
            $table->decimal('min_25', 8, 2)->nullable()->after('min_reales');
            $table->decimal('min_50', 8, 2)->nullable()->after('min_25');
            $table->decimal('total_min', 8, 2)->nullable()->after('min_50');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_solicitud_hes', function (Blueprint $table) {
            $table->dropColumn(['min_reales', 'min_25', 'min_50', 'total_min']);
        });
        
        Schema::table('tbl_solicitud_hes', function (Blueprint $table) {
            $table->bigInteger('min_reales')->nullable()->after('id_tipoCompensacion');
            $table->bigInteger('min_25')->nullable()->after('min_reales');
            $table->bigInteger('min_50')->nullable()->after('min_25');
            $table->bigInteger('total_min')->nullable()->after('min_50');
        });
    }
};
