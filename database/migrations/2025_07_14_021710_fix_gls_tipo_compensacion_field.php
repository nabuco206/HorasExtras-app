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
        Schema::table('tbl_tipo_compensacions', function (Blueprint $table) {
            $table->string('gls_tipoCompensacion', 255)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_tipo_compensacions', function (Blueprint $table) {
            $table->bigInteger('gls_tipoCompensacion')->change();
        });
    }
};
