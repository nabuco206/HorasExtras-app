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
        Schema::table('tbl_personas', function (Blueprint $table) {
            $table->boolean('flag_lider')->default(false)->comment('Indica si la persona puede ser líder');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_personas', function (Blueprint $table) {
            $table->dropColumn('flag_lider');
        });
    }
};
