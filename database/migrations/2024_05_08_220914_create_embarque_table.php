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
        Schema::create('embarque', function (Blueprint $table) {
            $table->id(); // clave primaria
            $table->string('numEconomico')->nullable(); // número económico (puede ser nulo)
            $table->boolean('entregado')->nullable(); // estado de entrega (puede ser nulo)
            $table->boolean('Desaduanado')->nullable(); // estado de desaduanamiento (puede ser nulo)
            $table->string('claveNombre')->nullable(); // clave de nombre (puede ser nulo)
            $table->string('tipoOper')->nullable(); // tipo de operación (puede ser nulo)
            $table->string('claveAduana')->nullable(); // clave de aduana (puede ser nulo)
            $table->date('fechaEmbarque'); // fecha de embarque (obligatorio)
            $table->timestamps(); // para created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embarque');
    }
};
