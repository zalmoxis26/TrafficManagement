<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id(); // clave primaria
            $table->string('clave')->unique(); // clave única
            $table->string('nombre'); // descripción de la empresa
            $table->string('rfc')->unique(); // RFC único
            $table->timestamps(); // columnas de timestamp
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
