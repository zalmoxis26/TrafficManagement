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
        Schema::create('pedimento', function (Blueprint $table) {
            $table->id(); // clave primaria
            $table->string('numPedimento'); // número de pedimento
            $table->string('aduana')->nullable(); ; // nombre de la aduana
            $table->string('patente')->nullable(); ; // patente de aduana
            $table->string('clavePed')->nullable(); ; // clave del pedimento
            $table->date('fechaPed'); 
            $table->string('adjunto')->nullable(); // archivo adjunto (puede ser nulo)
            $table->timestamps(); // timestamp para created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedimento');
    }
};
