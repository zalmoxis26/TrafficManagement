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
        Schema::table('revisiones', function (Blueprint $table) {
            $table->string('ubicacionRevision')->nullable()->default("SIN UBICACION"); // Cambia el tipo de dato según tus necesidades
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('revisiones', function (Blueprint $table) {
            //
        });
    }
};
