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
        Schema::create('traficos', function (Blueprint $table) {
            $table->id(); // clave primaria
            $table->foreignId('cliente_id')->nullable(); // clave foránea
            $table->string('operacion')->nullable(); // operación
            $table->foreignId('embarque_id')->nullable(); // clave foránea
            $table->string('folioTransporte')->nullable(); // folio de transporte
            $table->date('fechaReg'); // fecha de registro (obligatorio)
            $table->string('Toperacion')->nullable(); // tipo de operación
            $table->string('factura'); // factura (obligatorio)
            $table->string('clavePed')->nullable(); // clave de pedimento
            $table->boolean('usDocs')->nullable(); // uso de documentos
            $table->boolean('Revision')->nullable(); // revisión
            $table->string('Transporte')->nullable(); // transporte
            $table->string('Clasificacion')->nullable(); // clasificación
            $table->string('Odt')->nullable(); // orden de transporte
            $table->timestamps(); // timestamp
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traficos');
    }
};
