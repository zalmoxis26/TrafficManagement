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
        Schema::table('embarque', function (Blueprint $table) {
            $table->string('Caat', 4)->nullable();
            $table->string('Placas')->nullable();
            $table->string('Transporte')->nullable();
            $table->string('TipoDeTransporte')->nullable();
            $table->string('anden')->nullable();
            $table->string('chofer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('embarque', function (Blueprint $table) {
            //
        });
    }
};
